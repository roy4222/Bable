import os
import discord
from discord.ext import commands
import mysql.connector
from datetime import datetime
import aiohttp
import asyncio

# 從環境變數中獲取 Token
TOKEN = os.getenv('DISCORD_TOKEN')  # 或直接寫入 Token：TOKEN = '你的 Discord Token'

intents = discord.Intents.default()
intents.message_content = True  # 確保能讀取訊息內容
intents.messages = True  # 確保能讀取訊息

bot = commands.Bot(command_prefix="!", intents=intents)

# 設定你要抓取圖片的頻道 ID
TARGET_CHANNEL_ID = 1213391839508570134  # 替換為你的頻道 ID

# 定義圖片保存的路徑
SAVE_DIRECTORY = "C:/xampp/htdocs/dc/images"  # 替換為你的網站圖片資料夾

# 如果保存圖片的文件夾不存在，創建它
if not os.path.exists(SAVE_DIRECTORY):
    os.makedirs(SAVE_DIRECTORY)

# 連接到 MySQL 資料庫
conn = mysql.connector.connect(
    host="localhost",      # MySQL 伺服器地址
    user="root",           # 資料庫使用者
    password="27003378",   # 資料庫密碼
    database="dc_bot2"     # 資料庫名稱
)
cursor = conn.cursor()

# 非同步下載圖片函數
async def download_image(url, file_path):
    async with aiohttp.ClientSession() as session:
        async with session.get(url) as response:
            if response.status == 200:
                with open(file_path, 'wb') as file:
                    file.write(await response.read())
                print(f"圖片已保存到 {file_path}")
            else:
                print(f"下載圖片失敗：{url}")

@bot.event
async def on_ready():
    print(f'Logged in as {bot.user}')
    
    # 抓取指定頻道的歷史訊息
    channel = bot.get_channel(TARGET_CHANNEL_ID)
    
    if channel:
        print(f"正在監聽頻道 {channel.name} 的圖片...")
        
        # 抓取頻道的訊息
        async for message in channel.history(limit=100):
            await process_message(message)

@bot.event
async def on_message(message):
    await process_message(message)

async def process_message(message):
    if message.attachments:
        for attachment in message.attachments:
            if attachment.content_type.startswith("image/"):
                print(f"抓到圖片：{attachment.url}")
                message_id = message.id
                unique_file_name = f"{message_id}_{attachment.filename}"

                cursor.execute('''
                    SELECT COUNT(*) FROM images WHERE image_name = %s
                ''', (unique_file_name,))
                result = cursor.fetchone()

                if result[0] > 0:
                    print(f"圖片已存在，跳過: {unique_file_name}")
                    continue

                file_path = os.path.join(SAVE_DIRECTORY, unique_file_name)
                await download_image(attachment.url, file_path)

                download_time = datetime.now()

                cursor.execute('''
                    INSERT INTO images (image_name, image_path, author, message_time, download_time) 
                    VALUES (%s, %s, %s, %s, %s)
                ''', (unique_file_name, file_path, str(message.author), message.created_at, download_time))
                conn.commit()

bot.run(TOKEN)
