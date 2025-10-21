# Appled Project - Digital Twin Dashboard

A university research project demonstrating Adaptive Multi-Factor Authentication (AMFA) capabilities using Agentic AI to secure IoT devices. This digital twin dashboard connects to water sensors via MQTT, providing real-time monitoring with AI-powered adaptive security through voice recognition and TOTP authentication.

## Table of Contents

- [Features](#features)
- [System Requirements](#system-requirements)
- [Dependencies](#dependencies)
- [Installation](#installation)
- [Configuration](#configuration)
- [Running the Application](#running-the-application)
- [Development](#development)
- [Testing](#testing)
- [Related Repositories](#related-repositories)

## Features

- Real-time IoT sensor monitoring via MQTT
- Digital twin visualization with Google Maps integration
- Adaptive Multi-Factor Authentication (AMFA) with:
  - Voice recognition
  - TOTP (Time-based One-Time Password)
- Agentic AI powered security decisions
- WebSocket real-time updates via Laravel Reverb
- Role-based access control

## System Requirements

### Minimum Requirements

- **PHP**: 8.2 or higher
- **PostgreSQL**: 12 or higher
- **Node.js**: 18 or higher
- **Composer**: 2.x
- **NPM**: 9 or higher

### Additional Services

- **MQTT Broker**: Required for IoT sensor communication
  - Recommended: [MqttProxy](https://github.com/BCHLP/MqttProxy) (built for this dashboard)
  - Alternative: Any MQTT-compatible broker (Mosquitto, HiveMQ, etc.)
- **Laravel Reverb**: For WebSocket broadcasting (included in dependencies)

## Dependencies

This project requires the following API keys and services:

### Required API Keys

1. **Anthropic API Key** - For Agentic AI capabilities
   - Sign up at: https://console.anthropic.com/

2. **Google Maps API Key** - For digital twin visualization
   - Get yours at: https://console.cloud.google.com/
   - Required APIs: Maps JavaScript API, Geocoding API

3. **OpenAI API Key** - For AI-powered features
   - Sign up at: https://platform.openai.com/

4. **MaxMind License Key** - For geo-location database updates
   - Register at: https://www.maxmind.com/
   - Required for IP-based location services

5. **Resend API Key** - For email notifications (optional)
   - Sign up at: https://resend.com/

### Voice Recognition Service

The voice recognition AMFA feature requires the companion Python service:
- Repository: https://github.com/BCHLP/voice-recognition
- Follow the setup instructions in that repository to obtain your `VOICE_TOKEN`

## Installation

### 1. Clone the Repository

```bash
git clone <your-repository-url>
cd project
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install Node Dependencies

```bash
npm install
```

### 4. Setup Environment Configuration

```bash
cp .env.example .env
```

Edit the `.env` file and configure the required variables (see [Configuration](#configuration) section below).

### 5. Generate Application Key

```bash
php artisan key:generate
```

### 6. Setup Database

Create a PostgreSQL database:

```bash
# Example using psql
createdb appled_dashboard
```

Update your `.env` file with PostgreSQL credentials:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=appled_dashboard
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

Run migrations:

```bash
php artisan migrate
```

### 7. Setup MaxMind GeoIP Database

After obtaining your MaxMind license key, update the geo-location database:

```bash
php artisan geoip:update
```

### 8. Build Frontend Assets

```bash
npm run build
```

## Configuration

### Essential Environment Variables

Edit your `.env` file with the following required configurations:

#### Application Settings

```env
APP_NAME="Appled Project"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
```

#### Database Configuration

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=appled_dashboard
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

#### API Keys

```env
# Anthropic AI
ANTHROPIC_API_KEY="your-anthropic-api-key"

# Google Maps
VITE_GOOGLE_MAPS_API_KEY="your-google-maps-api-key"

# OpenAI
OPENAI_API_KEY="your-openai-api-key"

# MaxMind
MAXMIND_LICENSE_KEY="your-maxmind-license-key"

# Resend (Optional)
RESEND_KEY="your-resend-key"
```

#### Laravel Reverb Configuration

```env
REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_SERVER_HOST="0.0.0.0"
REVERB_SERVER_PORT=8080
REVERB_HOST="localhost"
REVERB_PORT=8080
REVERB_SCHEME="http"

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"

BROADCAST_CONNECTION=reverb
```

Generate Reverb credentials:

```bash
php artisan reverb:install
```

#### MQTT Configuration

```env
MQTT_HOST="localhost"
MQTT_PORT=8883
```

Ensure your MQTT broker is running and accessible at the configured host and port.

#### Voice Recognition & AMFA

First, setup the [voice-recognition](https://github.com/BCHLP/voice-recognition) service, then configure:

```env
VOICE_REGISTER="http://your-voice-recognition-server/voice/register"
VOICE_COMPARE="http://your-voice-recognition-server/voice/compare"
VOICE_TOKEN="token-from-voice-recognition-repository"
ADAPTIVE_MFA_ENDPOINT="http://your-voice-recognition-server/action/login"
AMFA_ENABLED=true
```

#### Session & Cache

```env
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

## Running the Application

### Development Mode

The easiest way to run the application in development:

```bash
composer dev
```

This command concurrently runs:
- PHP development server (port 8000)
- Queue worker
- Log viewer (Laravel Pail)
- Vite development server

### Manual Development Setup

Alternatively, run each service individually in separate terminal windows:

#### Terminal 1: Web Server
```bash
php artisan serve
```

#### Terminal 2: Laravel Reverb (WebSocket Server)
```bash
php artisan reverb:start
```

#### Terminal 3: Queue Worker
```bash
php artisan queue:work
```

#### Terminal 4: Frontend Assets
```bash
npm run dev
```

### Production Build

For production deployment:

```bash
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Then configure your web server (Nginx, Apache, etc.) to serve the application.

## Development

### Code Quality

Run code formatter:
```bash
npm run format
```

Check code formatting:
```bash
npm run format:check
```

Run linter:
```bash
npm run lint
```

PHP code style (using Laravel Pint):
```bash
./vendor/bin/pint
```

### Type Checking

```bash
npm run types
```

## Testing

Run the test suite:

```bash
composer test
```

Or directly with Pest:

```bash
php artisan test
```

For specific tests:

```bash
php artisan test --filter TestName
```

## Related Repositories

This project works in conjunction with the following repositories (available under the same GitHub account):

- **Voice Recognition Service**: https://github.com/BCHLP/voice-recognition
  - Python-based voice recognition for AMFA
  - Required for voice authentication features

- **MQTT Proxy**: https://github.com/BCHLP/MqttProxy
  - Custom MQTT broker built for this dashboard
  - Handles IoT sensor communication

## Project Structure

```
.
├── app/                    # Application logic
├── database/              # Migrations, seeders, factories
├── resources/
│   ├── js/               # React frontend components
│   └── css/              # Stylesheets
├── routes/               # API and web routes
├── tests/                # Test suite
├── public/               # Public assets
└── storage/              # Logs, cache, uploaded files
```

## Troubleshooting

### Common Issues

**MQTT Connection Failed**
- Ensure MQTT broker is running on the configured host and port
- Check firewall settings for port 8883
- Verify MQTT credentials if authentication is enabled

**Reverb WebSocket Not Connecting**
- Ensure Reverb server is running: `php artisan reverb:start`
- Check that ports match between server and client configuration
- Verify `BROADCAST_CONNECTION=reverb` in `.env`

**Voice Recognition Not Working**
- Confirm the voice-recognition service is running
- Verify `VOICE_TOKEN` matches the token generated in the voice-recognition repository
- Check endpoint URLs are correct and accessible

**MaxMind GeoIP Errors**
- Run `php artisan geoip:update` to download the latest database
- Verify your MaxMind license key is active

## License

MIT License - This is a university research project.

## Support

For issues or questions, please open an issue in the respective repository.
