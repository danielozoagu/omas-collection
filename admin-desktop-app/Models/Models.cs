using System.Collections.Generic;
using Newtonsoft.Json;

namespace OmasAdminApp.Models
{
    public class User
    {
        [JsonProperty("id")]         public int    Id        { get; set; }
        [JsonProperty("username")]   public string Username  { get; set; } = "";
        [JsonProperty("email")]      public string Email     { get; set; } = "";
        [JsonProperty("role")]       public string Role      { get; set; } = "";
        [JsonProperty("created_at")] public string CreatedAt { get; set; } = "";
    }

    public class Product
    {
        [JsonProperty("id")]          public int     Id          { get; set; }
        [JsonProperty("name")]        public string  Name        { get; set; } = "";
        [JsonProperty("category")]    public string  Category    { get; set; } = "";
        [JsonProperty("price")]       public decimal Price       { get; set; }
        [JsonProperty("stock")]       public int     Stock       { get; set; }
        [JsonProperty("image")]       public string? Image       { get; set; }
        [JsonProperty("description")] public string? Description { get; set; }
        [JsonProperty("created_at")]  public string  CreatedAt   { get; set; } = "";
        public string FormattedPrice => $"\u20a6{Price:N0}";
        public string StockDisplay   => Stock <= 5 ? $"\u26a0 {Stock} left" : Stock.ToString();
        public string ImageUrl       => string.IsNullOrEmpty(Image)
            ? "" : $"http://localhost/OMAS-COLLECTION-BACKEND/uploads/{Image}";
    }

    public class OrderItem
    {
        [JsonProperty("product_id")] public int     ProductId { get; set; }
        [JsonProperty("name")]       public string  Name      { get; set; } = "";
        [JsonProperty("quantity")]   public int     Quantity  { get; set; }
        [JsonProperty("price")]      public decimal Price     { get; set; }
        [JsonProperty("image")]      public string? Image     { get; set; }
        public string FormattedPrice    => $"\u20a6{Price:N0}";
        public string FormattedSubtotal => $"\u20a6{Price * Quantity:N0}";
    }

    public class Order
    {
        [JsonProperty("id")]              public int              Id             { get; set; }
        [JsonProperty("username")]        public string           Username       { get; set; } = "";
        [JsonProperty("email")]           public string           Email          { get; set; } = "";
        [JsonProperty("total_amount")]    public decimal          TotalAmount    { get; set; }
        [JsonProperty("status")]          public string           Status         { get; set; } = "Pending";
        [JsonProperty("payment_status")]  public string           PaymentStatus  { get; set; } = "Unpaid";
        [JsonProperty("tracking_number")] public string?          TrackingNumber { get; set; }
        [JsonProperty("created_at")]      public string           CreatedAt      { get; set; } = "";
        [JsonProperty("items")]           public List<OrderItem>  Items          { get; set; } = new();
        public string FormattedTotal => $"\u20a6{TotalAmount:N0}";
        public string ItemCount      => $"{Items.Count} item{(Items.Count != 1 ? "s" : "")}";
    }

    public class DashboardStats
    {
        [JsonProperty("total_revenue")]   public decimal     TotalRevenue   { get; set; }
        [JsonProperty("total_orders")]    public int         TotalOrders    { get; set; }
        [JsonProperty("total_products")]  public int         TotalProducts  { get; set; }
        [JsonProperty("total_customers")] public int         TotalCustomers { get; set; }
        [JsonProperty("pending_orders")]  public int         PendingOrders  { get; set; }
        [JsonProperty("recent_orders")]   public List<Order> RecentOrders   { get; set; } = new();
        public string FormattedRevenue => $"\u20a6{TotalRevenue:N0}";
    }

    public class ApiResponse<T>
    {
        [JsonProperty("success")] public bool   Success { get; set; }
        [JsonProperty("message")] public string Message { get; set; } = "";
        public T?                               Data    { get; set; }
    }

    public class LoginData
    {
        [JsonProperty("user")]  public User   User  { get; set; } = new();
        [JsonProperty("token")] public string Token { get; set; } = "";
    }
}
