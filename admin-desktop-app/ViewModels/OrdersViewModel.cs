using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.Linq;
using System.Threading.Tasks;
using System.Windows.Input;
using OmasAdminApp.Models;
using OmasAdminApp.Services;

namespace OmasAdminApp.ViewModels
{
    public class OrdersViewModel : BaseViewModel
    {
        private List<Order> _allOrders = new();

        private ObservableCollection<Order> _orders = new();
        public ObservableCollection<Order> Orders { get => _orders; set => Set(ref _orders, value); }

        private Order? _selected;
        public Order? SelectedOrder
        {
            get => _selected;
            set
            {
                if (Set(ref _selected, value) && value != null)
                {
                    NewStatus        = value.Status;
                    NewPaymentStatus = value.PaymentStatus;
                    NewTracking      = value.TrackingNumber ?? "";
                }
            }
        }

        private string _statusFilter = "All";
        public string StatusFilter
        {
            get => _statusFilter;
            set
            {
                if (Set(ref _statusFilter, value))
                    ApplyFilter();
            }
        }

        private string _searchText = "";
        public string SearchText
        {
            get => _searchText;
            set
            {
                if (Set(ref _searchText, value))
                    ApplyFilter();
            }
        }

        private string _newStatus        = "Pending";
        private string _newPaymentStatus = "Unpaid";
        private string _newTracking      = "";

        public string NewStatus        { get => _newStatus;        set => Set(ref _newStatus,        value); }
        public string NewPaymentStatus { get => _newPaymentStatus; set => Set(ref _newPaymentStatus, value); }
        public string NewTracking      { get => _newTracking;      set => Set(ref _newTracking,      value); }

        public List<string> FilterOptions { get; } = new()
        {
            "All", "Pending", "Processing", "Shipped", "Completed", "Declined", "Cancelled"
        };

        public List<string> StatusOptions { get; } = new()
        {
            "Pending", "Processing", "Shipped", "Completed", "Declined", "Cancelled"
        };

        public List<string> PaymentOptions { get; } = new()
        {
            "Unpaid", "Pending", "Paid", "Successful", "Failed", "Refunded"
        };

        public ICommand LoadCommand         { get; }
        public ICommand UpdateStatusCommand { get; }

        public OrdersViewModel()
        {
            LoadCommand         = new RelayCommand(async () => await LoadAsync());
            UpdateStatusCommand = new RelayCommand(async () => await UpdateStatusAsync());
        }

        public async Task LoadAsync()
        {
            IsBusy = true;
            StatusMessage = "Loading orders from web store...";
            try
            {
                var res = await ApiService.Instance.GetOrdersAsync();
                if (res.Success && res.Data != null)
                {
                    // Snapshot previously selected order ID before list is replaced
                    var previouslySelectedId = SelectedOrder?.Id;

                    _allOrders = res.Data;
                    ApplyFilter();

                    // Restore the selection after reload so the sidebar stays visible
                    if (previouslySelectedId.HasValue)
                    {
                        var restored = Orders.FirstOrDefault(o => o.Id == previouslySelectedId.Value);
                        if (restored != null)
                        {
                            // Directly set the backing field to avoid re-triggering side effects
                            _selected = restored;
                            OnPropertyChanged(nameof(SelectedOrder));
                        }
                    }

                    StatusMessage = $"{_allOrders.Count} total store orders loaded.";
                }
                else
                {
                    StatusMessage = res.Message ?? "Failed to load orders.";
                }
            }
            catch (Exception ex)
            {
                StatusMessage = $"Failed to load orders: {ex.Message}";
            }
            finally
            {
                IsBusy = false;
            }
        }

        private void ApplyFilter()
        {
            var filtered = _allOrders.AsEnumerable();

            if (!string.IsNullOrEmpty(StatusFilter) && StatusFilter != "All")
            {
                filtered = filtered.Where(o => string.Equals(o.Status, StatusFilter, StringComparison.OrdinalIgnoreCase));
            }

            if (!string.IsNullOrWhiteSpace(SearchText))
            {
                var q = SearchText.Trim().ToLowerInvariant();
                filtered = filtered.Where(o =>
                    o.Id.ToString().Contains(q) ||
                    o.Username.ToLowerInvariant().Contains(q) ||
                    o.Email.ToLowerInvariant().Contains(q) ||
                    (o.TrackingNumber != null && o.TrackingNumber.ToLowerInvariant().Contains(q))
                );
            }

            Orders = new ObservableCollection<Order>(filtered.ToList());
        }

        private async Task UpdateStatusAsync()
        {
            if (SelectedOrder == null)
            {
                StatusMessage = "Please select an order from the list first.";
                return;
            }

            // Capture the order ID and details NOW before anything async
            var orderId      = SelectedOrder.Id;
            var statusToSet  = NewStatus;
            var paymentToSet = NewPaymentStatus;
            var trackingToSet = NewTracking?.Trim() ?? "";

            // Guard: ensure we have a valid status selected
            if (string.IsNullOrWhiteSpace(statusToSet))
            {
                StatusMessage = "Please choose a fulfillment status before updating.";
                return;
            }
            if (string.IsNullOrWhiteSpace(paymentToSet))
            {
                StatusMessage = "Please choose a payment status before updating.";
                return;
            }

            IsBusy = true;
            StatusMessage = $"Updating Order #{orderId}...";
            try
            {
                var res = await ApiService.Instance.UpdateOrderStatusAsync(
                    orderId, statusToSet, paymentToSet, trackingToSet);

                if (res.Success)
                {
                    // Reload list (which will now restore SelectedOrder too)
                    await LoadAsync();
                    StatusMessage = $"? Order #{orderId} updated to '{statusToSet}' successfully!";
                }
                else
                {
                    StatusMessage = $"Update failed: {res.Message}";
                }
            }
            catch (Exception ex)
            {
                StatusMessage = $"Update error: {ex.Message}";
            }
            finally
            {
                IsBusy = false;
            }
        }
    }
}
