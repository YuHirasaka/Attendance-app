# 勤怠管理アプリ(Attendance-App)

ユーザーの勤怠と管理を行う勤怠管理アプリ

## 主な機能
> - **ユーザー**会員登録・ログイン・ログアウト
> - 勤怠の打刻（出勤・休憩・退勤）
> - 勤務情報取得
> - 勤怠の修正申請
> - 申請のステータス確認（承認待ち・承認済み）
> - **管理者**　ログイン・ログアウト
> - 日時勤怠一覧の確認
> - 各勤怠の詳細を確認・修正
> - スタッフ一覧とスタッフ毎の月次勤怠一覧の確認


## 環境構築

1. リポジトリをクローン
```bash
git clone git@github.com:YuHirasaka/Attendance-app.git
cd Attendance-app
```
2. Dockerを起動する
3. プロジェクト直下で、以下のコマンドを実行する
```bash
make init
```
※ `make init` を実行すると、以下の処理が自動で実行されます。

- Dockerコンテナの起動
- Composerパッケージのインストール
- `.env` ファイルの作成
- アプリケーションキーの生成
- データベースのマイグレーション
- シーディングによる初期データの投入
---
## メール認証
メール認証機能の確認には Mailtrap を使用しています。
以下のURLから会員登録し、Inbox を作成してください。
https://mailtrap.io/

.env に以下を設定してください。
```
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
```
---
## 使用技術

- docker
- php　8.1.34
- nginx 1.21.1
- mysql 8.0.26
- laravel 8.83.29
- mailtrap

---

## URL

- 開発環境：http://localhost/login
- 管理者ログイン：http://localhost/admin/login
- phpMyAdmin：http://localhost:8080/
- Mailtrap（メール確認用サンドボックス）：https://mailtrap.io/inboxes

## テーブル仕様
### usersテーブル
| カラム名 | 型 | primary key | unique key | not null | foreign key |
| --- | --- | --- | --- | --- | --- |
| id | bigint | ◯ |  | ◯ |  |
| name | varchar(20) |  |  | ◯ |  |
| email | varchar(255) |  | ◯ | ◯ |  |
| password | varchar(255) |  |  | ◯ |  |
| role | enum(user.admin) |  |  | ◯ |  |
| email_verified_at | timestamp |  |  |  |  |
| remember_token | varchar(100) |  |  |  |  |
| created_at | timestamp |  |  |  |  |
| updated_at | timestamp |  |  |  |  |


### attendancesテーブル
| カラム名 | 型 | primary key | unique key | not null | foreign key |
| --- | --- | --- | --- | --- | --- |
| id | bigint | ◯ |  | ◯ |  |
| user_id | bigint |  |  | ◯ | users(id) |
| work_date | date |  |  | ◯ |  |
| check_in | time |  |  |  |  |
| check_out | time |  |  |  |  |
| note | varchar(255) |  |  |  |  |
| created_at | timestamp |  |  |  |  |
| updated_at | timestamp |  |  |  |  |
UNIQUE(user_id, work_date)

### attendance_breaksテーブル
| カラム名 | 型 | primary key | unique key | not null | foreign key |
| --- | --- | --- | --- | --- | --- |
| id | bigint | ◯ |  | ◯ |  |
| attendance_id | bigint |  |  | ◯ | attendances(id) |
| break_start | time |  |  |  |  |
| break_end | time |  |  |  |  |
| created_at | timestamp |  |  |  |  |
| updated_at | timestamp |  |  |  |  |

### attendance_correctionsテーブル
| カラム名 | 型 | primary key | unique key | not null | foreign key |
| --- | --- | --- | --- | --- | --- |
| id | bigint | ◯ |  | ◯ |  |
| attendance_id | bigint |  | ◯ | ◯ | attendances(id) |
| requested_check_in | time |  |  | ◯ |  |
| requested_check_out | time |  |  | ◯ |  |
| reason | varchar(255) |  |  | ◯ |  |
| status | enum(pending.approved) |  |  | ◯ |  |
| approved_by | bigint |  |  |  | users(id) |
| approved_at | timestamp |  |  |  |  |
| created_at | timestamp |  |  |  |  |
| updated_at | timestamp |  |  |  |  |

### attendance_correction_breaksテーブル
| カラム名 | 型 | primary key | unique key | not null | foreign key |
| --- | --- | --- | --- | --- | --- |
| id | bigint | ◯ |  | ◯ |  |
| attendance_correction_id | bigint |  |  | ◯ | attendance_corrections(id) |
| requested_break_start | time |  |  | ◯ |  |
| requested_break_end | time |  |  | ◯  |  |
| created_at | timestamp |  |  |  |  |
| updated_at | timestamp |  |  |  |  |

## ER図
![ER図](er.png)

## テストアカウント


### 管理者ユーザー
| 名前 | メールアドレス | パスワード |
| --- | --- | --- |
| 勤怠管理者 | admin@gmail.com | password |

### 一般ユーザー

| 名前 | メールアドレス | パスワード |
| --- | --- | --- |
| 西 怜奈 | reina.n@coachtech.com | password |
| 山田 太郎 | taro.y@coachtech.com | password |
| 増田 一世 | issei.m@coachtech.com | password |
| 山本 敬吉 | keikichi.y@coachtech.com | password |
| 秋田 朋美 | tomomi.a@coachtech.com | password |
| 中西 教夫 | norio.n@coachtech.com | password |


## php unitテスト
SQLite のインメモリデータベースを使用してテストを実行しています。
```bash
php artisan test
```