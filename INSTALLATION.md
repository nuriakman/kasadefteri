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

## Getting Client ID from Google Cloud Console

1. Go to [Google Cloud Console](https://console.cloud.google.com) and sign in with your Google account.

2. Click on the "Select Project" button in the top right and create a "New Project"

3. After creating the project, select "APIs & Services" > "OAuth consent screen" from the left menu

   - Choose "External" as the User Type
   - Fill in the required application information
   - Add the necessary scopes (email, profile)
   - Add test users if you're in testing mode
   - Click "Save and Continue"

4. Go to "Credentials" in the left menu and click "Create Credentials" > "OAuth client ID"

   - Choose "Web application" as the application type
   - Add a name for your OAuth client
   - Add authorized JavaScript origins (e.g., <http://localhost:9000>, <https://yourdomain.com>)
   - Add authorized redirect URIs if needed
   - Click "Create"

5. Copy the generated Client ID and add it to your `.env` file:

   ```text
   GOOGLE_CLIENT_ID=your-client-id-here
   ```

6. Make sure to keep your Client ID secure and never commit it to version control

## Google Cloud Console'dan Client ID Alma

1. [Google Cloud Console](https://console.cloud.google.com)'a gidin ve Google hesabınızla oturum açın.

2. Sağ üstteki "Proje Seç" düğmesine tıklayın ve "Yeni Proje" oluşturun

3. Projeyi oluşturduktan sonra, sol menüden "APIs & Services" > "OAuth consent screen" seçin

   - Kullanıcı Türü olarak "External" seçin
   - Gerekli uygulama bilgilerini doldurun
   - Gerekli kapsamları ekleyin (email, profile)
   - Test modundaysanız test kullanıcılarını ekleyin
   - "Kaydet ve Devam Et"e tıklayın

4. Sol menüden "Credentials"a gidin ve "Create Credentials" > "OAuth client ID"ye tıklayın

   - Uygulama türü olarak "Web application" seçin
   - OAuth istemciniz için bir isim ekleyin
   - İzin verilen JavaScript kaynaklarını ekleyin (örn: <http://localhost:9000>, <https://sizinalan.com>)
   - Gerekirse izin verilen yönlendirme URI'larını ekleyin
   - "Oluştur"a tıklayın

5. Oluşturulan Client ID'yi kopyalayın ve `.env` dosyanıza ekleyin:

   ```text
   GOOGLE_CLIENT_ID=sizin-client-id-niz
   ```

6. Client ID'nizi güvende tutun ve asla sürüm kontrolüne commit etmeyin

## Mobil Uygulama

Android ve iOS uygulamaları Quasar Framework kullanılarak oluşturulmuştur. Derleme için:

```bash
# Android için
quasar build -m android

# iOS için
quasar build -m ios
```
