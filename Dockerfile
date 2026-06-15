# ============================================================
#  Dockerfile - Plant Care Diary (Laravel 12 + Medoo + MySQL)
#  Jeden kontener: nginx + php-fpm (gotowy pod Azure App Service)
# ============================================================

# ---------- Etap 1: budowa frontendu (Vite) ----------
FROM node:20-alpine AS assets
WORKDIR /app
# kopiujemy tylko to, co potrzebne do zbudowania assetów
COPY package.json package-lock.json* ./
RUN npm install
COPY vite.config.js ./
COPY resources ./resources
COPY public ./public
RUN npm run build

# ---------- Etap 2: aplikacja PHP + nginx ----------
FROM webdevops/php-nginx:8.2

# nginx ma serwować katalog public/ (front controller Laravela)
ENV WEB_DOCUMENT_ROOT=/app/public
WORKDIR /app

# kod aplikacji
COPY . /app

# zależności PHP (bez pakietów developerskich, zoptymalizowane)
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# zbudowany frontend z Etapu 1
COPY --from=assets /app/public/build /app/public/build

# uprawnienia dla katalogów, do których Laravel zapisuje
RUN chown -R application:application /app \
 && chmod -R 775 storage bootstrap/cache

EXPOSE 80
