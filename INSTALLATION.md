# Kurulum Kılavuzu

## Teknolojik Altyapı

### Frontend

- TypeScript
- Vue 3 (Composition API with Script Setup)
- Quasar Framework
- Node.js 20.11

### Backend

- PHP 7.4
- MariaDB
- Redis
- PDO

### DevOps

- Ubuntu 20.04

## Kurulum

### Gereksinimler

- Node.js 20.11
- PHP 7.4
- MariaDB
- Redis
- Composer
- Git

### Backend Kurulumu

#### 1. Repoyu klonlayın

```bash
git clone https://github.com/kullanici/kasadefteri.git
cd kasadefteri/api
```

#### 2. Composer bağımlılıklarını yükleyin

```bash
composer install
```

#### 3. `.env` dosyasını oluşturun

```bash
cp .env.example .env
```

#### 4. `.env` dosyasını düzenleyin

```env
DB_HOST=localhost
DB_NAME=kasadefteri
DB_USER=kullanici
DB_PASS=sifre

REDIS_HOST=localhost
REDIS_PORT=6379

JWT_SECRET=guclu-bir-jwt-secret-key

GOOGLE_CLIENT_ID=your-google-client-id
GOOGLE_CLIENT_SECRET=your-google-client-secret
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback

FRONTEND_URL=http://localhost:9000
```

#### 5. Veritabanını oluşturun

```bash
mysql -u root -p
CREATE DATABASE kasadefteri;
exit;
```

#### 6. Veritabanı tablolarını oluşturun

```bash
mysql -u root -p kasadefteri < database.sql
```

### Frontend Kurulumu

#### Frontend dizinine gidin

```bash
cd ../src
```

#### NPM bağımlılıklarını yükleyin

```bash
npm install
```

#### `.env` dosyasını oluşturun

```bash
cp .env.example .env
```

#### `.env` dosyasını düzenleyin

```env
VITE_API_URL=http://localhost:8000
VITE_GOOGLE_CLIENT_ID=your-google-client-id
```

### Uygulamayı Çalıştırma

#### Backend'i başlatın

```bash
cd api
php -S localhost:8000 -t public
```

#### Frontend'i başlatın

```bash
cd src
quasar dev
```

Uygulama <http://localhost:9000> adresinde çalışmaya başlayacaktır.

## Güvenlik Önlemleri

- JWT tabanlı kimlik doğrulama
- CSRF koruması
- XSS koruması
- Rate limiting
- Input validasyonu
- Rol bazlı erişim kontrolü
- Güvenli HTTP başlıkları
- SSL/TLS desteği

## Mobil Uygulama

Android ve iOS uygulamaları Quasar Framework kullanılarak oluşturulmuştur. Derleme için:

```bash
# Android için
quasar build -m android

# iOS için
quasar build -m ios
```
