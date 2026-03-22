# 勤怠管理アプリ

## 概要

出退勤・休憩時間を管理するWebアプリケーションです。  
一般ユーザーが打刻・勤怠修正申請を行い、管理者が確認・承認できます。

## 環境構築

### Dockerビルド
- git clone <リンク>
- docker-compose up -d --build

### Laravel環境構築
- docker-compose exec php bash
- composer install
- cp .env.example .env
- php artisan key:generate
- php artisan migrate
- php artisan db:seed

## 開発環境
- アプリ http://localhost/
- phpMyAdmin http://localhost:8080
- MailHog（メール確認） http://localhost:8025

## ログイン情報

`php artisan db:seed` で管理者・一般ユーザーの両方が作成されます。

### 管理者

管理者専用のログインページ（ http://localhost/admin/login ）にアクセスしてログインしてください。

| 項目 | 値 |
|---|---|
| メールアドレス | admin@example.com |
| パスワード | password123 |

### 一般ユーザー

| 項目 | 値 |
|---|---|
| メールアドレス | user@example.com |
| パスワード | password123 |

> `db:seed` 実行日を基準に、過去30日分の平日の勤怠ダミーデータが作成されます。

## 使用技術

| 種別 | バージョン |
|---|---|
| PHP | 8.1 |
| Laravel | 8.x |
| Laravel Fortify | 1.x |
| MySQL | 8.0.26 |
| Nginx | 1.21.1 |
| PHPUnit | 9.x |

## 主な機能

### 一般ユーザー

- 会員登録・ログイン・ログアウト（Laravel Fortify）
- メール認証
- 出勤・退勤・休憩の打刻
- 勤怠一覧・詳細の確認
- 勤怠修正申請

### 管理者

- ログイン・ログアウト（Laravel Fortify）
- 全スタッフの勤怠一覧・詳細確認
- スタッフ一覧・スタッフ別勤怠一覧
- 修正申請の一覧確認・承認
- スタッフ別勤怠データのCSVエクスポート
