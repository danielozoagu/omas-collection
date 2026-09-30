using System.Threading.Tasks;
using System.Windows.Input;
using OmasAdminApp.Services;

namespace OmasAdminApp.ViewModels
{
    public class LoginViewModel : BaseViewModel
    {
        private string _serverUrl = ApiService.Instance.BaseUrl;
        public string ServerUrl { get => _serverUrl; set { if (Set(ref _serverUrl, value)) ApiService.Instance.SetBaseUrl(value); } }

        private string _email    = "admin@omas.com";
        public string Email      { get => _email;     set => Set(ref _email,     value); }

        private string _password = "";
        public string Password   { get => _password;  set => Set(ref _password,  value); }

        private string _error    = "";
        public string ErrorMessage { get => _error;   set => Set(ref _error,     value); }

        public event System.Action? OnLoginSuccess;
        public ICommand LoginCommand { get; }

        public LoginViewModel()
        {
            LoginCommand = new RelayCommand(async () => await DoLoginAsync());
        }

        private async Task DoLoginAsync()
        {
            if (string.IsNullOrWhiteSpace(Email) || string.IsNullOrWhiteSpace(Password))
            { ErrorMessage = "Please enter email and password."; return; }

            IsBusy       = true;
            ErrorMessage = "";
            StatusMessage= "Authenticating...";
            try
            {
                ApiService.Instance.SetBaseUrl(ServerUrl);
                var res = await ApiService.Instance.LoginAsync(Email.Trim(), Password);
                if (res.Success) { StatusMessage = ""; OnLoginSuccess?.Invoke(); }
                else ErrorMessage = res.Message.Length > 0 ? res.Message : "Invalid credentials or not an admin account.";
            }
            finally { IsBusy = false; StatusMessage = ""; }
        }
    }
}