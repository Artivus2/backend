<html><head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <title>Технология многопроцессорного сокета PHP в режиме реального времени push</title>
  <link href="/css/bootstrap.min.css" rel="stylesheet">
    <link href="/css/jquery-sinaEmotion-2.1.0.min.css" rel="stylesheet">
    <link href="/css/style.css" rel="stylesheet">
	
  <script type="text/javascript" src="/js/swfobject.js"></script>
  <script type="text/javascript" src="/js/web_socket.js"></script>
  <script type="text/javascript" src="/js/jquery.min.js"></script>
    <script type="text/javascript" src="/js/jquery-sinaEmotion-2.1.0.min.js"></script>

  <script type="text/javascript">
    if (typeof console == "undefined") {    this.console = { log: function (msg) {  } };}
    // Если браузер не поддерживает веб-сокет, эта флэш-память будет использоваться для автоматической имитации протокола веб-сокета. Этот процесс прозрачен для разработчиков
    WEB_SOCKET_SWF_LOCATION = "/swf/WebSocketMain.swf";
    // Включить отладку Flash WebSocket
    WEB_SOCKET_DEBUG = true;
    var ws, name, client_list={},room_id,client_id;

    room_id = getQueryString('room_id')?getQueryString('room_id'):1;

    function getQueryString(name) {
        var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
        var r = window.location.search.substr(1).match(reg);
        if (r != null) return unescape(r[2]); return null;
    } 

    // Подключиться к серверу
    function connect() {
       // 创建websocket
       ws = new WebSocket("ws://"+document.domain+":7272");
       // Когда откроется соединение с сокетом, введите имя пользователя
       ws.onopen = onopen;
       // При наличии сообщения отображается различная информация в зависимости от типа сообщения.
       ws.onmessage = onmessage; 
       ws.onclose = function() {
    	  console.log("Соединение закрыто, запланировано повторное подключение");
          connect();
       };
       ws.onerror = function() {
     	  console.log("Произошла ошибка");
       };
    }

    // Отправлять данные для входа при установке соединения
    function onopen()
    {
        if(!name)
        {
            show_prompt();
        }
        // 登录
        var login_data = '{"type":"login","client_name":"'+name.replace(/"/g, '\\"')+'","room_id":'+room_id+'}';
        console.log("Рукопожатие веб-сокета прошло успешно, и данные для входа отправлены:"+login_data);
        ws.send(login_data);
    }

    // Когда сервер отправляет сообщение
    function onmessage(e)
    {
        console.log(e.data);
        var data = JSON.parse(e.data);
        switch(data['type']){
            // Сервер пингует клиента
            case 'ping':
                ws.send('{"type":"pong"}');
                break;;
            //Войти Обновить список пользователей
            case 'login':
                var client_name = data['client_name'];
                if(data['client_list'])
                {
                    client_id = data['client_id'];
                    client_name = 'ты';
                    client_list = data['client_list'];
                }
                else
                {
                    client_list[data['client_id']] = data['client_name']; 
                }

                say(data['client_id'], data['client_name'],  client_name+' Присоединился к чату', data['time']);

                flush_client_list();
                console.log(data['client_name']+"Авторизация успешна");
                break;
            // 发言
            case 'say':
                //{"type":"say","from_client_id":xxx,"to_client_id":"all/client_id","content":"xxx","time":"xxx"}
                say(data['from_client_id'], data['from_client_name'], data['content'], data['time']);
                break;
            // 用户退出 更新用户列表
            case 'logout':
                //{"type":"logout","client_id":xxx,"time":"xxx"}
                say(data['from_client_id'], data['from_client_name'], data['from_client_name']+' покинул', data['time']);
                delete client_list[data['from_client_id']];
                flush_client_list();
        }
    }

    // 输入姓名
    function show_prompt(){  
        name = prompt('Введите ваше имя: ', '');
        if(!name || name=='null'){  
            name = 'гость';
        }
    }  

    // 提交对话
    function onSubmit() {
      var input = document.getElementById("textarea");
      var to_client_id = $("#client_list option:selected").attr("value");
      var to_client_name = $("#client_list option:selected").text();
      ws.send('{"type":"say","to_client_id":"'+to_client_id+'","to_client_name":"'+to_client_name+'","content":"'+input.value.replace(/"/g, '\\"').replace(/\n/g,'\\n').replace(/\r/g, '\\r')+'"}');
      input.value = "";
      input.focus();
    }

    // Обновить список пользователей
    function flush_client_list(){
    	var userlist_window = $("#userlist");
    	var client_list_slelect = $("#client_list");
    	userlist_window.empty();
    	client_list_slelect.empty();
    	userlist_window.append('<h4>онлайн</h4><ul>');
    	client_list_slelect.append('<option value="all" id="cli_all">каждый</option>');
    	for(var p in client_list){
            userlist_window.append('<li id="'+p+'">'+client_list[p]+'</li>');
            if (p!=client_id) {
                client_list_slelect.append('<option value="'+p+'">'+client_list[p]+'</option>');   
            }
        }
    	$("#client_list").val(select_client_id);
    	userlist_window.append('</ul>');
    }

    // 发言
    function say(from_client_id, from_client_name, content, time){
        //Разбираем картинки Sina Weibo
        content = content.replace(/(http|https):\/\/[\w]+.sinaimg.cn[\S]+(jpg|png|gif)/gi, function(img){
            return "<a target='_blank' href='"+img+"'>"+"<img src='"+img+"'>"+"</a>";}
        );

        //解析url
        content = content.replace(/(http|https):\/\/[\S]+/gi, function(url){
            if(url.indexOf(".sinaimg.cn/") < 0)
                return "<a target='_blank' href='"+url+"'>"+url+"</a>";
            else
                return url;
        }
        );

    	$("#dialog").append('<div class="speech_item"><img src="http://lorempixel.com/38/38/?'+from_client_id+'" class="user_icon" /> '+from_client_name+' <br> '+time+'<div style="clear:both;"></div><p class="triangle-isosceles top">'+content+'</p> </div>').parseEmotion();
    }

    $(function(){
    	select_client_id = 'all';
	    $("#client_list").change(function(){
	         select_client_id = $("#client_list option:selected").attr("value");
	    });
        $('.face').click(function(event){
            $(this).sinaEmotion();
            event.stopPropagation();
        });
    });


  </script>
</head>
<body onload="connect();">
    <div class="container">
	    <div class="row clearfix">
	        <div class="col-md-1 column">
	        </div>
	        <div class="col-md-6 column">
	           <div class="thumbnail">
	               <div class="caption" id="dialog"></div>
	           </div>
	           <form onsubmit="onSubmit(); return false;">
	                <select style="margin-bottom:8px" id="client_list">
                        <option value="all">каждый</option>
                    </select>
                    <textarea class="textarea thumbnail" id="textarea"></textarea>
                    <div class="say-btn">
                        <input type="button" class="btn btn-default face pull-left" value="выражение" />
                        <input type="submit" class="btn btn-default" value="публиковать" />
                    </div>
               </form>
               <div>
               &nbsp;&nbsp;&nbsp;&nbsp;<b>Список номеров:</b>（Сейчас в комнате<script>document.write(room_id)</script>）<br>
               &nbsp;&nbsp;&nbsp;&nbsp;<a href="/?room_id=1">Комната 1</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="/?room_id=2">Комната 2</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="/?room_id=3">3</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="/?room_id=4">4</a>
               <br><br>
               </div>
               <p class="cp">PHP多进程+Websocket(HTML5/Flash)+PHP Socket实时推送技术&nbsp;&nbsp;&nbsp;&nbsp;Powered by <a href="http://www.workerman.net/workerman-chat" target="_blank">workerman-chat</a></p>
	        </div>
	        <div class="col-md-3 column">
	           <div class="thumbnail">
                   <div class="caption" id="userlist"></div>
               </div>
               <a href="http://workerman.net:8383" target="_blank"><img style="width:252px;margin-left:5px;" src="/img/workerman-todpole.png"></a>
	        </div>
	    </div>
    </div>
    <script type="text/javascript">var _bdhmProtocol = (("https:" == document.location.protocol) ? " https://" : " http://");document.write(unescape("%3Cscript src='" + _bdhmProtocol + "hm.baidu.com/h.js%3F7b1919221e89d2aa5711e4deb935debd' type='text/javascript'%3E%3C/script%3E"));</script>
    <script type="text/javascript">
      // Динамический адаптивный экран
      document.write('<meta name="viewport" content="width=device-width,initial-scale=1">');
      $("textarea").on("keydown", function(e) {
          //Нажмите клавишу ввода, чтобы отправить автоматически.
          if(e.keyCode === 13 && !e.ctrlKey) {
              e.preventDefault();
              $('form').submit();
              return false;
          }

          // Нажмите комбинацию клавиш Ctrl+Enter, чтобы изменить строку.
          if(e.keyCode === 13 && e.ctrlKey) {
              $(this).val(function(i,val){
                  return val + "\n";
              });
          }
      });
    </script>
</body>
</html>
