# ArtworkMapping
美術品の損傷を管理するServer-Client型Webシステム

## 概要
![main](https://user-images.githubusercontent.com/14792604/104145367-1265ce00-540a-11eb-97c2-a45f7c8a456a.png)

本システムは、美術品等の損傷を定形データ化し管理することで、損傷管理の効率化・データの利活用を目的とするものです。

特長
- Server-Client型であるため職場LAN内展開が可能
- Docker化されており展開が容易
- 使いやすさを重視したシンプルなUI
- Excelエクスポート機能があり、Excel狂信者老害対策は万全

## 導入
1. ダウンロード or git clone
```
git clone https://github.com/Kajune/ArtworkMapping
```
2. Dockerイメージのビルド
```
docker-compose build
```
3. Dockerコンテナの起動
```
docker-compose up
```
4. DBの初期値セット(パスワードを聞かれるので、以下の「DBユーザパスワード」を入力してください)
```
docker\setup_db.bat #Windows
./docker/setup_db.sh #Linux
```
5. 利用開始(localhost:15010等にアクセス)
編集モードの初期パスワードは「akagisannkawaii」です

## ユーザ名・パスワード等の設定
.envファイルの以下の変数を設定
- 編集モードパスワード: EDITHASH (Default: akagisannkawaii、設定変更の際はsha256のハッシュを設定)
- DBユーザパスワード: MYSQL_PASSWORD (Default: akagisannkawaii)
- ポート番号: PORT (Default: 15010)

## メンテナンス
サーバを一時的に停止させる場合は、以下を実行
```
docker-compose stop
```

サーバを再起動させる場合は、以下を実行
```
docker-compose start
```

サーバを完全に停止させる場合は、以下を実行
```
docker-compose down
```

DBのバックアップを作成する場合は、以下を実行
```
docker\backup.bat #Windows
./docker/backup.sh #Linux
```

バックアップからDBをリストアする場合は、以下を実行
```
docker\restore.bat #Windows
./docker/restore.sh #Linux
```
