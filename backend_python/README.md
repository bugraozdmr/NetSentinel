# Servis ve Port Tarayıcı

Bu proje, sunuculardaki açık portları ve servisleri taramak için geliştirilmiştir.

## Kurulum

Aşağıdaki adımları izleyerek sanal ortamı oluşturun ve bağımlılıkları yükleyin:

```bash
python3.12 -m venv env
source env/bin/activate
pip install -r requirements.txt
```

## Uygulamayı Çalıştırmak

```bash
uvicorn app.main:app --reload
```

Uygulama, varsayılan olarak `http://127.0.0.1:8000` adresinde çalışacaktır.
