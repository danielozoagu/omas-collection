using System.Threading.Tasks;
using System.Windows.Input;
using OmasAdminApp.Services;
using OmasAdminApp.Models;

namespace OmasAdminApp.ViewModels
{
    public class MainViewModel : BaseViewModel
    {
        public DashboardViewModel  DashboardVM  { get; } = new();
        public ProductsViewModel   ProductsVM   { get; } = new();
        public OrdersViewModel     OrdersVM     { get; } = new();
        public CustomersViewModel  CustomersVM  { get; } = new();

        private object? _currentView;
        public object? CurrentView { get => _currentView; set => Set(ref _currentView, value); }

        private string _section = "Dashboard";
        public string ActiveSection { get => _section; set => Set(ref _section, value); }

        public User? CurrentUser => ApiService.Instance.CurrentUser;

        public ICommand NavigateCommand { get; }
        public ICommand LogoutCommand   { get; }

        public event System.Action? OnLogoutRequested;

        public MainViewModel()
        {
            NavigateCommand = new RelayCommand(param =>
            {
                if (param is string s) NavigateTo(s);
            });
            LogoutCommand = new RelayCommand(async () =>
            {
                await ApiService.Instance.LogoutAsync();
                OnLogoutRequested?.Invoke();
            });
            NavigateTo("Dashboard");
        }

        public void NavigateTo(string section)
        {
            ActiveSection = section;
            CurrentView = section switch
            {
                "Dashboard" => DashboardVM,
                "Products"  => ProductsVM,
                "Orders"    => OrdersVM,
                "Customers" => CustomersVM,
                _           => DashboardVM
            };

            // Automatically refresh data when switching tabs
            _ = Task.Run(async () =>
            {
                switch (section)
                {
                    case "Dashboard":
                        await DashboardVM.LoadAsync();
                        break;
                    case "Products":
                        await ProductsVM.LoadAsync();
                        break;
                    case "Orders":
                        await OrdersVM.LoadAsync();
                        break;
                    case "Customers":
                        await CustomersVM.LoadAsync();
                        break;
                }
            });
        }

        public async Task RefreshAllAsync()
        {
            await System.Threading.Tasks.Task.WhenAll(
                DashboardVM.LoadAsync(),
                ProductsVM.LoadAsync(),
                OrdersVM.LoadAsync(),
                CustomersVM.LoadAsync()
            );
        }
    }
}