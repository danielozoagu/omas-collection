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
    public class ProductsViewModel : BaseViewModel
    {
        private List<Product> _allProducts = new();

        private ObservableCollection<Product> _products = new();
        public ObservableCollection<Product> Products { get => _products; set => Set(ref _products, value); }

        private Product? _selected;
        public Product? SelectedProduct { get => _selected; set => Set(ref _selected, value); }

        private bool _showForm;
        public bool ShowForm { get => _showForm; set => Set(ref _showForm, value); }

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

        private string _formName = "";
        private string _formCategory = "Bags";
        private string _formDesc = "";
        private string _formPriceText = "";
        private string _formStockText = "10";
        private string? _imagePath;
        private bool _isEditing;
        private int _editId;

        public string FormTitle => _isEditing ? "Edit Product" : "Add New Product";
        public string FormName      { get => _formName;      set => Set(ref _formName,      value); }
        public string FormCategory  { get => _formCategory;  set => Set(ref _formCategory,  value); }
        public string FormDesc      { get => _formDesc;      set => Set(ref _formDesc,      value); }
        public string FormPriceText { get => _formPriceText; set => Set(ref _formPriceText, value); }
        public string FormStockText { get => _formStockText; set => Set(ref _formStockText, value); }
        public string? ImagePath    { get => _imagePath;    set => Set(ref _imagePath,    value); }

        public decimal FormPrice
        {
            get => decimal.TryParse(_formPriceText, out var v) ? v : 0;
            set => FormPriceText = value.ToString("0.##");
        }
        public int FormStock
        {
            get => int.TryParse(_formStockText, out var v) ? v : 0;
            set => FormStockText = value.ToString();
        }

        public List<string> CategoryList { get; } = new()
        {
            "Bags", "Watches", "Accessories", "Shoes", "Clothing", "Jewelry", "Caps", "General"
        };

        public ICommand LoadCommand      { get; }
        public ICommand AddCommand       { get; }
        public ICommand EditCommand      { get; }
        public ICommand SaveCommand      { get; }
        public ICommand DeleteCommand    { get; }
        public ICommand CancelCommand    { get; }
        public ICommand PickImageCommand { get; }

        public ProductsViewModel()
        {
            LoadCommand      = new RelayCommand(async () => await LoadAsync());
            AddCommand       = new RelayCommand(() =>
            {
                SelectedProduct = null;
                OpenForm(null);
            });
            EditCommand      = new RelayCommand(() =>
            {
                if (SelectedProduct != null)
                {
                    OpenForm(SelectedProduct);
                }
                else
                {
                    StatusMessage = "Please select a product from the list to edit.";
                }
            });
            SaveCommand      = new RelayCommand(async () => await SaveAsync());
            DeleteCommand    = new RelayCommand(async () => await DeleteAsync());
            CancelCommand    = new RelayCommand(() => ShowForm = false);
            PickImageCommand = new RelayCommand(PickImage);
        }

        public void OpenForm(Product? p)
        {
            _isEditing    = p != null;
            _editId       = p?.Id ?? 0;
            FormName      = p?.Name        ?? "";
            FormCategory  = !string.IsNullOrEmpty(p?.Category) ? p.Category : "Bags";
            FormDesc      = p?.Description ?? "";
            FormPriceText = p != null ? p.Price.ToString("0.##", System.Globalization.CultureInfo.InvariantCulture) : "";
            FormStockText = p != null ? p.Stock.ToString() : "10";
            ImagePath     = null;
            OnPropertyChanged(nameof(FormTitle));
            ShowForm      = true;
            StatusMessage = _isEditing ? $"Editing '{FormName}'" : "Entering new product details...";
        }

        private void PickImage()
        {
            var dlg = new Microsoft.Win32.OpenFileDialog
            {
                Filter = "Image Files|*.jpg;*.jpeg;*.png;*.webp;*.gif"
            };
            if (dlg.ShowDialog() == true)
            {
                ImagePath = dlg.FileName;
            }
        }

        public async Task LoadAsync()
        {
            IsBusy = true;
            StatusMessage = "Fetching store products...";
            try
            {
                var res = await ApiService.Instance.GetProductsAsync();
                if (res.Success && res.Data != null)
                {
                    _allProducts = res.Data;
                    ApplyFilter();
                    StatusMessage = $"{_allProducts.Count} products active in store.";
                }
                else
                {
                    StatusMessage = res.Message;
                }
            }
            catch (Exception ex)
            {
                StatusMessage = $"Load failed: {ex.Message}";
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
                Products = new ObservableCollection<Product>(_allProducts);
            }
            else
            {
                var q = SearchText.Trim().ToLowerInvariant();
                var filtered = _allProducts.Where(p =>
                    p.Name.ToLowerInvariant().Contains(q) ||
                    p.Category.ToLowerInvariant().Contains(q) ||
                    p.Id.ToString().Contains(q)
                ).ToList();
                Products = new ObservableCollection<Product>(filtered);
            }
        }

        private async Task SaveAsync()
        {
            if (string.IsNullOrWhiteSpace(FormName))
            {
                StatusMessage = "Please enter a product name.";
                return;
            }

            var cleanPrice = FormPriceText?.Replace(",", "").Trim() ?? "0";
            if (!decimal.TryParse(cleanPrice, System.Globalization.NumberStyles.Any, System.Globalization.CultureInfo.InvariantCulture, out var price) &&
                !decimal.TryParse(cleanPrice, out price))
            {
                StatusMessage = "Please enter a valid price (e.g. 250000).";
                return;
            }

            if (!int.TryParse(FormStockText?.Trim(), out var stock))
            {
                stock = 10;
            }

            IsBusy = true;
            StatusMessage = "Saving product to store...";
            try
            {
                var p = new Product
                {
                    Id          = _editId,
                    Name        = FormName.Trim(),
                    Category    = FormCategory,
                    Price       = price,
                    Stock       = stock,
                    Description = FormDesc.Trim()
                };

                var res = _isEditing
                    ? await ApiService.Instance.UpdateProductAsync(p, ImagePath)
                    : await ApiService.Instance.AddProductAsync(p, ImagePath);

                if (res.Success)
                {
                    ShowForm = false;
                    await LoadAsync();
                    StatusMessage = _isEditing ? "Product updated successfully!" : "New product added to store!";
                }
                else
                {
                    StatusMessage = res.Message;
                }
            }
            catch (Exception ex)
            {
                StatusMessage = $"Save error: {ex.Message}";
            }
            finally
            {
                IsBusy = false;
            }
        }

        private async Task DeleteAsync()
        {
            if (SelectedProduct == null)
            {
                StatusMessage = "Please select a product from the list to delete.";
                return;
            }

            IsBusy = true;
            StatusMessage = $"Deleting {SelectedProduct.Name}...";
            try
            {
                var res = await ApiService.Instance.DeleteProductAsync(SelectedProduct.Id);
                if (res.Success)
                {
                    await LoadAsync();
                    StatusMessage = "Product deleted.";
                }
                else
                {
                    StatusMessage = res.Message;
                }
            }
            catch (Exception ex)
            {
                StatusMessage = $"Delete error: {ex.Message}";
            }
            finally
            {
                IsBusy = false;
            }
        }
    }
}
