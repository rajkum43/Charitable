# Hosting पर Config.php को Fix करने के लिए

## समस्या
Hosting पर `getPDOConnection()` function नहीं है, इसलिए API काम नहीं कर रहा है।

## समाधान
अपने hosting के `includes/config.php` फाइल में यह function add करें।

### Step 1: अपने hosting के config.php को खोलें

### Step 2: मौजूदा code के बाद यह function add करें

```php
/**
 * Get PDO Connection
 * Helper function to access the PDO connection from config
 * Works on both local and hosting environments
 */
function getPDOConnection() {
    // First check if $pdo already exists in globals
    if (isset($GLOBALS['pdo']) && $GLOBALS['pdo'] instanceof PDO) {
        return $GLOBALS['pdo'];
    }
    
    // Try to access global $pdo variable
    global $pdo;
    if (isset($pdo) && $pdo instanceof PDO) {
        $GLOBALS['pdo'] = $pdo;
        return $pdo;
    }
    
    // If not exists, try to create new connection
    try {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        $GLOBALS['pdo'] = $pdo;
        error_log('New PDO connection created');
        return $pdo;
    } catch (PDOException $e) {
        error_log('getPDOConnection Error: ' . $e->getMessage());
        return null;
    }
}
```

### Step 3: Function कहाँ add करें?

आपके hosting के config.php में यह code पहले से है:

```php
try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    
} catch (PDOException $e) {
    error_log('Database Connection Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}
```

**इसके तुरंत बाद** (लेकिन `exit;` से पहले) ऊपर वाला function add करें।

### Step 4: Verify करें

URL खोलें:
```
https://brctbharat.com/api/get_active_poll_alerts.php?debug=1
```

### Expected Response (अगर सब ठीक है):

```json
{
  "success": true,
  "data": [...],
  "debug": {
    "current_date": "2026-05-10",
    "poll_count_fetched": 2,
    "alerts_generated": 2,
    "date_filter_used": true,
    "total_records_in_response": 2,
    "database_stats": {
      "poll_total": 2,
      "poll_with_alert": 2,
      "beti_with_poll_status": 2,
      "death_with_poll_status": 0,
      "total_donations": 4
    }
  }
}
```

---

## Debugging Tips

### अगर `debug=1` के साथ भी data नहीं आ रहा है:

1. **Check hosting के error log:**
   - `logs/errors.log` खोलें और last 10 lines देखें

2. **Database check करें:**
   - phpMyAdmin में जाकर देखें कि `poll` table में alert >= 1 वाले कितने records हैं

3. **Check करें कि table हैं या नहीं:**
   ```
   SELECT * FROM poll LIMIT 5;
   SELECT * FROM beti_vivah_aavedan LIMIT 5;
   SELECT * FROM donation_transactions LIMIT 5;
   ```

---

## Files Updated

✅ Local: `/includes/config.php` - Already has `getPDOConnection()` function
❌ Hosting: `/includes/config.php` - Need to add `getPDOConnection()` function (see above)
✅ Local & Hosting: `/api/get_active_poll_alerts.php` - Updated to handle both scenarios
