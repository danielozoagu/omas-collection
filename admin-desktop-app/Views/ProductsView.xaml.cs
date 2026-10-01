using System.Windows.Controls;
using System.Windows.Input;
using OmasAdminApp.ViewModels;

namespace OmasAdminApp.Views
{
    public partial class ProductsView : UserControl
    {
        public ProductsView()
        {
            InitializeComponent();
        }

        private void DataGrid_MouseDoubleClick(object sender, MouseButtonEventArgs e)
        {
            if (DataContext is ProductsViewModel vm && vm.SelectedProduct != null)
            {
                vm.OpenForm(vm.SelectedProduct);
            }
        }
    }
}