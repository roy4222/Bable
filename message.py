import os
import discord
from discord.ext import commands
import mysql.connector
from datetime import datetime
import asyncio

# 從環境變數中獲取 Token
TOKEN = os.getenv('DISCORD_TOKEN')  # 或直接硬編碼 Token：TOKEN = '你的 Discord Token'

intents = discord.Intents.default()
intents.message_content = True  # 確保能讀取訊息內容
intents.messages = True  # 確保能讀取訊息

bot = commands.Bot(command_prefix="!", intents=intents)

# 設定你要抓取訊息的頻道 ID
TARGET_CHANNEL_ID = 941691569797480450  # 替換為你的頻道 ID

# 連接到 MySQL 資料庫
conn = mysql.connector.connect(
    host="localhost",      # MySQL 伺服器地址
    user="root",           # 資料庫使用者
    password="27003378",   # 資料庫密碼
    database="dc_bot3"     # 資料庫名稱
)
cursor = conn.cursor()

# 創建訊息表格（如果尚不存在）
cursor.execute('''
    CREATE TABLE IF NOT EXISTS messages (
        id BIGINT PRIMARY KEY,
        author VARCHAR(100),
        content TEXT,
        message_time DATETIME,
        download_time DATETIME
    )
''')

@bot.event
async def on_ready():
    print(f'Logged in as {bot.user}')
    
    # 抓取指定頻道的歷史訊息
    channel = bot.get_channel(TARGET_CHANNEL_ID)
    
    if channel:
        print(f"正在從頻道 {channel.name} 抓取歷史訊息...")

        # 設置一個總抓取訊息數量的上限，例如 500 條
        total_count = 0
        message_limit = 100

        # 分批抓取歷史訊息，防止漏掉訊息
        last_message = None
        while total_count < message_limit:
            history = channel.history(limit=100, before=last_message)
            count = 0  # 計算此次抓取了多少訊息

            async for message in history:
                await process_message(message)
                last_message = message  # 將最後一條訊息設為 last_message
                count += 1
                total_count += 1

                # 如果已經達到總數量限制，則停止抓取
                if total_count >= message_limit:
                    break

            # 如果抓取的訊息少於 100，則說明沒有更多訊息
            if count < 100:
                break

            # 每次抓取後稍作延遲，防止觸發 API 速率限制
            await asyncio.sleep(2)

@bot.event
async def on_message(message):
    await process_message(message)

async def process_message(message):
    # 檢查訊息是否包含內容
    if message.content:
        print(f"抓到訊息：{message.content}")
        
        # 檢查資料庫中是否已存在相同的訊息（以 message.id 來判斷唯一性）
        cursor.execute('''
            SELECT COUNT(*) FROM messages WHERE id = %s
        ''', (message.id,))
        result = cursor.fetchone()

        if result[0] > 0:
            print(f"訊息已存在，跳過: {message.content}")
            return  # 如果訊息已存在，跳過

        # 獲取當前時間作為下載時間
        download_time = datetime.now()

        # 將訊息的內容插入到 MySQL 資料庫
        cursor.execute('''
            INSERT INTO messages (id, author, content, message_time, download_time) 
            VALUES (%s, %s, %s, %s, %s)
        ''', (message.id, str(message.author), message.content, message.created_at, download_time))
        conn.commit()

# 運行 Discord 機器人
bot.run(TOKEN)

# 關閉資料庫連接
@bot.event
async def on_disconnect():
    conn.close()

