# Donation Tracker

A web app for tracking donated equipment (laptops, routers, tablets, etc.) and verifying their status at remote locations via QR codes.

## Requirements

- Apache with PHP 8.0+
- MySQL or MariaDB
- `uploads/` folder writable by Apache

## Installation

1. Copy files to your Apache `htdocs` directory
2. Open `http://localhost/donation-tracker/install.php` in your browser
3. Enter your MySQL credentials and create an admin password
4. The installer creates a `.env` file with your DB credentials
5. Done — you'll be redirected to the login page

## Configuration

Credentials are stored in `.env` (not in git). A template is provided:

```
cp .env.example .env
```

Edit `.env` to change DB credentials, app name, or URL. The installer does this automatically on first run.

## How It Works

### Admin

- Add locations (schools, offices, sites)
- Add items (name, type, serial number, donor info) and assign them to locations
- Print QR codes and send them to the location
- Monitor the dashboard for overdue inspections (red = needs attention)
- Export all data as JSON

### Inspector (Person at Location)

No login needed. Just:

1. Scan the QR code on the device with your phone
2. Enter your name
3. Pick a status: Working / Damaged / Missing / Replaced
4. Optionally take a photo and add notes
5. Submit

The item status updates automatically.

### Donor

No login needed. Just:

1. Go to `/check.php`
2. Enter the email used at donation time
3. See all donated items, their status, and full inspection history

## File Structure

```
donation-tracker/
├── admin/
│   ├── dashboard.php       # Stats, overdue items, recent inspections
│   ├── items.php           # Add/edit/delete items, QR codes, JSON export
│   ├── locations.php       # Add/edit/delete locations
│   ├── inspections.php     # View all inspections with filters
│   └── json-view.php       # Visual table view of all item data
├── api/
│   └── qr.php              # QR code redirect (generates via free API)
├── config/
│   └── database.php        # DB connection + helper functions (reads .env)
├── includes/
│   ├── header.php          # Shared navbar and layout
│   └── footer.php          # Shared footer
├── uploads/                # Inspection photos (not in git)
├── .env                    # DB credentials (not in git, created by installer)
├── .env.example            # Template for .env (committed to git)
├── check.php               # Donor lookup page (public)
├── inspect.php             # Inspection form (public, accessed via QR)
├── item.php                # Item detail + history (public)
├── index.php               # Landing page
├── install.php             # Web installer
├── login.php               # Admin login
├── logout.php              # Admin logout
└── donation_tracker.sql    # Database schema
```

## Tech Stack

- PHP (vanilla, no framework)
- MySQL / MariaDB
- Bootstrap 5 (CDN)
- Bootstrap Icons (CDN)
- QR codes via [api.qrserver.com](https://api.qrserver.com)

## Security Notes

- `.env` keeps DB credentials out of git (excluded via `.gitignore`)
- `.env.example` is the only template committed to git
- `uploads/.htaccess` blocks PHP execution in the uploads folder
- CSRF protection on all admin forms
- Passwords stored with `password_hash()` / `password_verify()`
- Re-run `install.php` to reset the database (drops all tables)

## License

MIT
