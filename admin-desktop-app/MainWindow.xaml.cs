using System.Windows;
using OmasAdminApp.ViewModels;

namespace OmasAdminApp
{
    public partial class MainWindow : Window
    {
        private readonly MainViewModel _vm;

        public MainWindow()
        {
            InitializeComponent();
            _vm = new MainViewModel();
            DataContext = _vm;

            if (_vm.CurrentUser != null)
                AdminNameText.Text = _vm.CurrentUser.Username;

            _vm.OnLogoutRequested += () =>
            {
                new LoginWindow().Show();
                Close();
            };

            Loaded += async (_, _) => await _vm.RefreshAllAsync();
        }
    }
}
