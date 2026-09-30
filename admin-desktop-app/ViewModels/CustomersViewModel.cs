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
    public class CustomersViewModel : BaseViewModel
    {
        private List<User> _allCustomers = new();

        private ObservableCollection<User> _customers = new();
        public ObservableCollection<User> Customers { get => _customers; set => Set(ref _customers, value); }

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

        public ICommand LoadCommand { get; }

        public CustomersViewModel()
        {
            LoadCommand = new RelayCommand(async () => await LoadAsync());
        }

        public async Task LoadAsync()
        {
            IsBusy = true;
            StatusMessage = "Fetching customer records...";
            try
            {
                var res = await ApiService.Instance.GetCustomersAsync();
                if (res.Success && res.Data != null)
                {
                    _allCustomers = res.Data;
                    ApplyFilter();
                    StatusMessage = $"{_allCustomers.Count} registered customers.";
                }
                else
                {
                    StatusMessage = res.Message;
                }
            }
            catch (Exception ex)
            {
                StatusMessage = $"Failed to load customers: {ex.Message}";
            }
            finally
            {
                IsBusy = false;
            }
        }

        private void ApplyFilter()
        {
            if (string.IsNullOrWhiteSpace(SearchText))
            {
                Customers = new ObservableCollection<User>(_allCustomers);
            }
            else
            {
                var q = SearchText.Trim().ToLowerInvariant();
                var filtered = _allCustomers.Where(u =>
                    u.Id.ToString().Contains(q) ||
                    u.Username.ToLowerInvariant().Contains(q) ||
                    u.Email.ToLowerInvariant().Contains(q)
                ).ToList();
                Customers = new ObservableCollection<User>(filtered);
            }
        }
    }
}