using System;
using System.Collections.Generic;
using System.IO;
using System.Net.Http;
using System.Net.Http.Headers;
using System.Text;
using System.Threading.Tasks;
using Newtonsoft.Json;
using Newtonsoft.Json.Linq;
using OmasAdminApp.Models;

namespace OmasAdminApp.Services
{
    public class ApiService
    {
        private static ApiService? _instance;
        public static ApiService Instance => _instance ??= new ApiService();

        private readonly HttpClient _client;
        private string _baseUrl = "https://omas-backend-055z.onrender.com";
        private string? _token;

        public User? CurrentUser { get; private set; }
        public bool IsAuthenticated => CurrentUser != null && !string.IsNullOrEmpty(_token);
        public string BaseUrl => _baseUrl;

        private static string ConfigPath()
        {
            var dir = Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.ApplicationData), "OmasAdminApp");
            Directory.CreateDirectory(dir);
            return Path.Combine(dir, "server_url.txt");
        }

        private ApiService()
        {
            try
            {
                var cfg = ConfigPath();
                if (File.Exists(cfg))
                {
                    var saved = File.ReadAllText(cfg).Trim();
                    if (!string.IsNullOrWhiteSpace(saved)) _baseUrl = saved.TrimEnd('/');
                }
            }
            catch { }

            _client = new HttpClient(new HttpClientHandler { AllowAutoRedirect = true })
            {
                Timeout = TimeSpan.FromSeconds(30)
            };
            _client.DefaultRequestHeaders.Accept.Add(new MediaTypeWithQualityHeaderValue("application/json"));
        }

        public void SetBaseUrl(string url)
        {
            if (string.IsNullOrWhiteSpace(url)) return;
            _baseUrl = url.Trim().TrimEnd('/');
            try { File.WriteAllText(ConfigPath(), _baseUrl); } catch { }
        }

        private void SetToken(string token)
        {
            _token = token;
            _client.DefaultRequestHeaders.Remove("Authorization");
            if (!string.IsNullOrEmpty(token))
                _client.DefaultRequestHeaders.Add("Authorization", $"Bearer {token}");
        }

        private async Task<ApiResponse<T>> SendAsync<T>(HttpRequestMessage req, string? fallbackKey = null)
        {
            try
            {
                var resp    = await _client.SendAsync(req);
                var content = await resp.Content.ReadAsStringAsync();
                
                if (!string.IsNullOrWhiteSpace(content))
                {
                    content = content.Trim('\uFEFF', '\u200B', '\u0000', ' ', '\t', '\r', '\n');

                    if (content.StartsWith("<"))
                    {
                        return new ApiResponse<T>
                        {
                            Success = false,
                            Message = $"Server returned HTML error (HTTP {(int)resp.StatusCode} {resp.StatusCode}). Check backend connection in XAMPP."
                        };
                    }

                    try
                    {
                        var jObj = JObject.Parse(content);
                        var success = jObj["success"]?.Value<bool>() ?? resp.IsSuccessStatusCode;
                        var message = jObj["message"]?.Value<string>() ?? (success ? "Success" : $"HTTP {resp.StatusCode}");

                        T? data = default;

                        if (jObj["data"] != null)
                        {
                            data = jObj["data"]!.ToObject<T>();
                        }
                        else if (!string.IsNullOrEmpty(fallbackKey) && jObj[fallbackKey] != null)
                        {
                            data = jObj[fallbackKey]!.ToObject<T>();
                        }
                        else
                        {
                            // Try parsing the whole object as T (e.g. DashboardStats)
                            try
                            {
                                data = jObj.ToObject<T>();
                            }
                            catch { }
                        }

                        return new ApiResponse<T>
                        {
                            Success = success,
                            Message = message,
                            Data = data
                        };
                    }
                    catch (Exception jex)
                    {
                        return new ApiResponse<T>
                        {
                            Success = false,
                            Message = $"Response parse error: {jex.Message}"
                        };
                    }
                }

                return new ApiResponse<T>
                {
                    Success = resp.IsSuccessStatusCode,
                    Message = resp.IsSuccessStatusCode ? "Success" : $"HTTP {(int)resp.StatusCode} {resp.StatusCode}"
                };
            }
            catch (Exception ex)
            {
                return new ApiResponse<T>
                {
                    Success = false,
                    Message = $"Connection failed: {ex.Message}"
                };
            }
        }

        private HttpRequestMessage Post(string path, object body) =>
            new(HttpMethod.Post, $"{_baseUrl}/{path}")
            {
                Content = new StringContent(JsonConvert.SerializeObject(body), Encoding.UTF8, "application/json")
            };

        private HttpRequestMessage Get(string path) =>
            new(HttpMethod.Get, $"{_baseUrl}/{path}");

        // ============== AUTH ==============
        public async Task<ApiResponse<LoginData>> LoginAsync(string email, string password)
        {
            var res = await SendAsync<LoginData>(Post("admin_login.php", new { email, password }));
            if (res.Success && res.Data != null)
            {
                CurrentUser = res.Data.User;
                SetToken(res.Data.Token);
            }
            return res;
        }

        public async Task LogoutAsync()
        {
            CurrentUser = null;
            SetToken(string.Empty);
            await Task.CompletedTask;
        }

        // ============== DASHBOARD ==============
        public async Task<ApiResponse<DashboardStats>> GetDashboardAsync() =>
            await SendAsync<DashboardStats>(Get("admin_dashboard.php"));

        // ============== PRODUCTS ==============
        public async Task<ApiResponse<List<Product>>> GetProductsAsync() =>
            await SendAsync<List<Product>>(Get("admin_get_products.php"), "products");

        public async Task<ApiResponse<object>> DeleteProductAsync(int id) =>
            await SendAsync<object>(Post("delete_product.php", new { id }));

        public async Task<ApiResponse<object>> AddProductAsync(Product p, string? imagePath = null)
        {
            if (!string.IsNullOrEmpty(imagePath) && File.Exists(imagePath))
            {
                using var form = new MultipartFormDataContent();
                form.Add(new StringContent(p.Name),                       "name");
                form.Add(new StringContent(p.Category),                   "category");
                form.Add(new StringContent(p.Price.ToString()),            "price");
                form.Add(new StringContent(p.Stock.ToString()),            "stock");
                form.Add(new StringContent(p.Description ?? ""),          "description");
                var bytes = await File.ReadAllBytesAsync(imagePath);
                var fc    = new ByteArrayContent(bytes);
                fc.Headers.ContentType = new MediaTypeHeaderValue("image/jpeg");
                form.Add(fc, "image", Path.GetFileName(imagePath));
                var req = new HttpRequestMessage(HttpMethod.Post, $"{_baseUrl}/add_product.php") { Content = form };
                return await SendAsync<object>(req);
            }
            return await SendAsync<object>(Post("add_product.php", p));
        }

        public async Task<ApiResponse<object>> UpdateProductAsync(Product p, string? imagePath = null)
        {
            if (!string.IsNullOrEmpty(imagePath) && File.Exists(imagePath))
            {
                using var form = new MultipartFormDataContent();
                form.Add(new StringContent(p.Id.ToString()),               "id");
                form.Add(new StringContent(p.Name),                       "name");
                form.Add(new StringContent(p.Category),                   "category");
                form.Add(new StringContent(p.Price.ToString()),            "price");
                form.Add(new StringContent(p.Stock.ToString()),            "stock");
                form.Add(new StringContent(p.Description ?? ""),          "description");
                var bytes = await File.ReadAllBytesAsync(imagePath);
                var fc    = new ByteArrayContent(bytes);
                fc.Headers.ContentType = new MediaTypeHeaderValue("image/jpeg");
                form.Add(fc, "image", Path.GetFileName(imagePath));
                var req = new HttpRequestMessage(HttpMethod.Post, $"{_baseUrl}/update_product.php") { Content = form };
                return await SendAsync<object>(req);
            }
            return await SendAsync<object>(Post("update_product.php", p));
        }

        // ============== ORDERS ==============
        public async Task<ApiResponse<List<Order>>> GetOrdersAsync() =>
            await SendAsync<List<Order>>(Get("get_orders.php"), "orders");

        public async Task<ApiResponse<object>> UpdateOrderStatusAsync(int orderId, string status, string paymentStatus, string trackingNumber) =>
            await SendAsync<object>(Post("update_order_status.php", new
            {
                order_id        = orderId,
                status,
                payment_status  = paymentStatus,
                tracking_number = trackingNumber
            }));

        // ============== CUSTOMERS ==============
        public async Task<ApiResponse<List<User>>> GetCustomersAsync() =>
            await SendAsync<List<User>>(Get("admin_get_customers.php"), "customers");
    }
}