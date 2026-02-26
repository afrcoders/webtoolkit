<h1 align="center">WebToolKit</h1>

<p align="center">
  <strong>A comprehensive SEO and web tools platform</strong>
</p>

<p align="center">
  <a href="#features">Features</a> •
  <a href="#getting-started">Getting Started</a> •
  <a href="#tools">Tools</a> •
  <a href="#contributing">Contributing</a>
</p>

---

## Overview

WebToolKit is a feature-rich SEO and web utilities platform built with Laravel. It provides a wide range of tools for content optimization, image processing, PDF manipulation, and much more.

## Features

- **SEO Tools** — Keyword analysis, meta tag generators, sitemap tools
- **Image Processing** — Compression, conversion, resizing, OCR
- **PDF Tools** — Merge, split, compress, convert PDF files
- **Text Utilities** — Word counters, case converters, formatters
- **Code Tools** — Minifiers, beautifiers, validators
- **QR Code Generator** — Create custom QR codes
- **Multi-language** — Translatable interface
- **Theme System** — Customizable frontend themes
- **User Management** — Roles and permissions
- **Analytics** — Track tool usage and views

## Tech Stack

- **Framework:** Laravel 10.x
- **PHP:** 8.2+
- **Database:** MySQL 8.0
- **Cache/Queue:** Redis 7
- **OCR:** Tesseract
- **PDF:** iLovePDF, DomPDF
- **Containerization:** Docker & Docker Compose

## Getting Started

### Prerequisites

- Docker Desktop
- Git

### Quick Start

```bash
# Clone the repository
git clone https://github.com/dhtml/webtoolkit.git
cd webtoolkit

# Install with Make
make install

# Or manually
cp .env.example .env
docker compose up -d --build
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

Access the application at http://localhost:8080

### Commands

```bash
make dev      # Start development
make down     # Stop containers
make logs     # View logs
make shell    # App shell access
make fresh    # Fresh migration
make test     # Run tests
```

### Environment

```env
APP_NAME=KortexTools
DB_HOST=mysql
DB_DATABASE=kortextools
REDIS_HOST=redis
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
```

## Tools Categories

### SEO Tools
- Keyword Density Checker
- Meta Tag Generator
- Robots.txt Generator
- Sitemap Generator
- Backlink Checker

### Image Tools
- Image Compressor
- Image Converter
- Image Resizer
- OCR Text Extraction
- QR Code Generator

### PDF Tools
- PDF Merger
- PDF Splitter
- PDF Compressor
- PDF to Image
- Image to PDF

### Text Tools
- Word Counter
- Character Counter
- Case Converter
- Text Formatter
- Lorem Ipsum Generator

### Code Tools
- HTML Minifier
- CSS Minifier
- JavaScript Minifier
- JSON Formatter
- SQL Formatter

## Project Structure

```
kortextools/
├── app/
│   ├── Http/Controllers/
│   ├── Models/
│   ├── Services/
│   └── Tools/
├── config/
├── database/
├── docker/
├── lang/
├── resources/
│   └── views/
├── routes/
└── tests/
```

## Contributing

1. Fork the repository
2. Create feature branch (`git checkout -b feature/amazing`)
3. Commit changes (`git commit -m 'Add feature'`)
4. Push (`git push origin feature/amazing`)
5. Open Pull Request

## License

MIT License - see [LICENSE](LICENSE)
