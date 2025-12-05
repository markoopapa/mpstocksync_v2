# 🚀 MP Stock Sync Pro - PrestaShop Module

Professional stock synchronization module for PrestaShop 1.7, 1.8, 8.x, 9.x supporting:
- **eMAG Marketplace** - real-time stock sync
- **Trendyol Marketplace** - real-time stock sync  
- **Supplier Sync** - sync stock from supplier PrestaShop to your shops

## 📋 Features

### ✅ Marketplace Integration
- **eMAG API** - Full stock and price synchronization
- **Trendyol API** - Full stock and price synchronization
- **Auto-sync** - Real-time synchronization on stock changes
- **Manual sync** - Bulk sync all products
- **Logging** - Detailed sync logs with error tracking

### ✅ Supplier Sync
- **Multiple suppliers** - Support for multiple supplier sources
- **Database direct connect** - Connect directly to supplier database
- **API connect** - Connect via PrestaShop Web Service API
- **Multi-shop sync** - Sync to multiple target shops
- **Auto-matching** - Automatic product matching by SKU/EAN

### ✅ Advanced Features
- **Cron jobs** - Automated scheduled synchronization
- **Error handling** - Automatic retry on failures
- **Notifications** - Email notifications on errors
- **Detailed logs** - Full sync history and debugging
- **Multi-language** - Hungarian and English support

## 🛠️ Installation

### Method 1: ZIP Upload (Recommended)
1. Download the latest release ZIP file
2. Go to PrestaShop Admin → Modules → Module Manager
3. Click "Upload a module"
4. Select the ZIP file and install

### Method 2: Manual Installation
```bash
# Clone repository
git clone https://github.com/markoopapa/mpstocksync_v2.git

# Install dependencies
cd mpstocksync_v2
composer install --no-dev

# Copy to PrestaShop modules directory
cp -r mpstocksync_v2 /path/to/prestashop/modules/
