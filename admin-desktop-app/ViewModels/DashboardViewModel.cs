using System.Threading.Tasks;
using System.Windows.Input;
using OmasAdminApp.Models;
using OmasAdminApp.Services;

namespace OmasAdminApp.ViewModels
{
    public class DashboardViewModel : BaseViewModel
    {
        private DashboardStats? _stats;
        public DashboardStats? Stats { get => _stats; set => Set(ref _stats, value); }

        public ICommand RefreshCommand { get; }

        public DashboardViewModel()
        {
            RefreshCommand = new RelayCommand(async () => await LoadAsync());
        }

        public async Task LoadAsync()
        {
            IsBusy = true; StatusMessage = "Loading dashboard...";
            try
            {
                var res = await ApiService.Instance.GetDashboardAsync();
                if (res.Success) Stats = res.Data;
                else StatusMessage = res.Message;
            }
            finally { IsBusy = false; }
        }
    }
}
