# mockcase2
# 勤怠管理アプリ

## 環境構築

### Docker ビルド

1. git clone git@github.com:nakagawa-hayato/mockcase.git
2. docker-compose up -d build

### ＊ MySQL は、OS によって起動しない場合があるのでそれぞれの PC に合わせて docker-compose.yml ファイルを編集してください。

### Laravel 環境構築

1. docker-compose exec php bash
2. composer install
3. .env.example ファイルから.env を作成し、環境変数を変更
4. php artisan key:generate
5. php artisan migrate
6. php artisan db:seed

### メール認証について

- mailtrapというツールを使用しています。
- 以下のリンクから会員登録をしてください。　
- https://mailtrap.io/

## テーブル仕様

<img width="2257" height="1848" alt="Image" src="https://github.com/user-attachments/assets/97b6a05e-888d-4dff-88df-f50021a2e615" />

## ER 図

<img width="771" height="721" alt="Image" src="https://github.com/user-attachments/assets/06468581-d112-4aa3-a967-88ca32f6f617" />

## URL

- 開発環境：http:/localhost/
- phpMyAdmin : http://localhost:8080/

