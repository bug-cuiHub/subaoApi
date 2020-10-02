#coding=utf-8 
from selenium import webdriver
from selenium.webdriver.chrome.options import Options
import time
import sys
import os
import socket

# 数据
mobile_emulation = {"deviceName":"iPhone X"}
sleepTime = 10
name = -1
equnique = sys.argv[1]
ip = socket.gethostbyname(socket.gethostname())
Pcuri = "http://"+ip+":8080/#/webHome/" + equnique
Phoneuri = "http://"+ip+":8080/#/home/" + equnique

PhoneList = ['weatherinfo','populationinfo','economicinfo','environmeninfo']
# PcList = ['cseismicinfo', 'cweatherinfo','cpopulationinfo','ceconomicinfo','cenvironmeninfo','dashboard', 'worldEq']
# imgNameList = ['cthd', 'cseismicinfo', 'cweatherinfo', 'cpopulationinfo', 'ceconomicinfo', 'cenvironmeninfo', 'dashboard', 'worldEq', 'seismicinfo', 'thd','weatherinfo','populationinfo','economicinfo','environmeninfo','nearfz']
imgNameList = ['seismicinfo', 'weatherinfo','populationinfo','economicinfo','environmeninfo']

# 将图片保存到本地
def savePng(uri,i = 'null'):
    global name
    name+=1
    if i != 'null':
        browser.get(uri+'/'+i)
    time.sleep(sleepTime)
    scrpath = ".\\static\\uploads\\image\\" +equnique
    browser.save_screenshot(scrpath+'\\'+ str(imgNameList[name])+'.png')

# web端截图
# def PC(Pcuri):
#     firstP(Pcuri)
#     for i in PcList:
#         savePng(Pcuri, i)
#     browser.quit()

# 手机端截图
def Phone(Phoneuri):
    firstP(Phoneuri)
    for i in PhoneList:
        savePng(Phoneuri, i)
    browser.quit()

# 打开浏览器，判断是手机端还是web端
def begin(mobile_emulation = ''):
    if mobile_emulation == '':
        return webdriver.Chrome()   
    else :
        options = Options()
        options.add_experimental_option("mobileEmulation", mobile_emulation)
        return webdriver.Chrome(chrome_options=options)   

# 第一次打开的页面
def firstP(uri):
    # browser.maximize_window()
    browser.set_window_size(4096,2160)
    print(browser.get_window_size())
    browser.get(uri)
    savePng(uri, 'null')

# 程序开始运行
browser=begin()
# PC(Pcuri)
browser=begin(mobile_emulation)     
Phone(Phoneuri)
print('截图完成')