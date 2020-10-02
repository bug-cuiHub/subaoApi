const http = require('http');
const child_process = require('child_process')
const fs = require('fs');
const util = require('util');

var request = require("request");
var url = "http://172.17.130.160/subaoApi/public/index/Ftpcontroller/ftp";


http.createServer((req, res) => {
    res.setHeader("Access-Control-Allow-Origin", "*");
    // 允许其他域名访问
    res.writeHead(200, {
        'Content-Type': 'text/html'
    });
    let str = '';
    if (req.method.toUpperCase() == "POST") {

        // 监听缓冲区中的数据，不断从缓冲区中获取数据
        req.on("data", chunk => {
            str += chunk;
        });

        // 监听数据是否获取完成
        req.on("end", () => {
            str = JSON.parse(str).url
            // res.end("success");

            let batName = str + ".bat";
            let insertdata = "python ..\\public\\auto.py " + str;
            let imgdir = "./static/uploads/image/" + str;

            // 生成存储此截图的文件夹
            fs.mkdir(imgdir, function (error) {
                if (error) {
                    console.log('创建目录失败：\n' + error);
                    return false;
                }
                console.log('创建目录成功');
            })

            // 生成bat文件
            fs.writeFile(batName, insertdata, 'utf8', function (error) {
                if (error) {
                    console.log(error);
                    return false;
                }
                console.log('生成了bat文件并写入成功');
                // 查看bat文件内容
                fs.readFile(batName, 'utf8', function (error, data) {
                    if (error) {
                        console.log(error);
                        return false;
                    }
                    console.log('\n开始读取\n');
                    console.log(data.toString()); // 读取出所有行的信息  
                    console.log('\n读取结束\n');
                    // 运行bat文件
                    runBat().then(() => {
                        console.log("截图完毕！开始向FTP传输！");
                        res.end("截图完毕！开始向FTP传输！");

                        var requestData = str;
                        request({
                            url: url,
                            method: "POST",
                            json: true,
                            headers: {
                                "content-type": "application/json",
                            },
                            form:{
                                'requestData': str
                            }
                        }, function (error, response, body) {
                            if (!error && response.statusCode == 200) {
                                console.log(response) // 请求成功的处理逻辑
                            }
                        });

                        // 截图完毕后删除bat文件
                        fs.unlink(batName, (err) => {
                            if (err) throw err;
                        });
                    }).catch(err => {
                        console.log(err);
                    })
                })
            })

            // 执行bat文件
            const exec = util.promisify(child_process.exec);
            async function runBat() {
                const {
                    error,
                    stdout,
                    stderr
                } = await exec(batName);
                if (error) {
                    console.log("进程启动错误原因：" + error);
                    return false;
                }
            }
            //另一种启动bat的方式
            // child_process.exec(batName, {cwd:'C:/wamp64/www/subaoApi/public'}, function (error, stdout, stderr) {
            //     if (error !== null) {
            //         console.log("错误了：" + error)
            //     }else console.log("成功")
            //     // console.log('stdout: ' + stdout);
            //     // console.log('stderr: ' + stderr);
            // }
            // );

        });
    }

}).listen(3000, () => {
    console.log("your app is running http://localhost:3000")
})