import EventListener from '../utility/EventListener';
const SocketController = function(){
    let that = {};
    let socket = new WebSocket(defines.url);
    let _callBackFunc = [];
    let _callBackIndex = 0;
    let _event = EventListener({});
    socket.onopen = function(event) {
        
    };
    socket.onmessage = function (event) {
        cc.log(event);
        let jsonStr = event.data;
        let jsonData = JSON.parse(event.data);
        // if(!_callBackFunc.hasOwnProperty(jsonData.callBackIndex) || jsonData.callBackIndex == 0)
        // {
        //     cc.log('error');
        //     return;
        // }
            

        let cb = _callBackFunc[jsonData.callBackIndex];
        if(jsonData.code == 200)
            cb(null,jsonData.data);
        else if(jsonData.code == 201)
            _event.fire(jsonData.data.route,jsonData.data);
        else
            cb(jsonData.message);
    };
    socket.onclose = function(event){
        cc.log("server close");
    };
    const sendData = function(data,mod,cb=null){
        if(cb != null)
        {
            _callBackIndex++;
            _callBackFunc[_callBackIndex] = cb;
            data['callBackIndex'] = _callBackIndex
        }

        let route = mod.split(".");
        let dataNow = {
            class: route[0], 
            action: route[1], 
            content: data
        };

        socket.send(JSON.stringify(dataNow));
    };
    that.init = function(){

    };
    that.requestWxLogin = function(data,cb){
        sendData(data,'login.login',cb)
    };
    that.requestCreateRoom = function(data,cb){
        sendData(data,'room.create',cb);
    };
    that.requestJoinRoom = function(data,cb){
        sendData(data,'room.join',cb);
    };

    that.requestReady = function(data,cb){
        sendData(data,'room.ready',cb);
    };

    that.requestRobState = function(data,cb){
        sendData(data,'rob.state',cb);
    };

    that.requestPlayCard = function(data,cb){
        sendData(data,'play.play',cb);
    };

    that.onPlayerJoin = function(cb){
        _event.on('room.otherJoin',cb);
    };

    that.onPlayerReady = function(cb){
        _event.on('room.otherReady',cb);
    };

    that.onGameStart = function(cb){
        _event.on('room.start',cb);
    };

    that.onRobTurn = function(cb){
        _event.on('rob.turn',cb);
    };

    that.onRobOtherState = function(cb){
        _event.on('rob.otherState',cb);
    };

    that.onRobEnd = function(cb){
        _event.on('rob.end',cb);
    };

    that.onPlayTurn = function(cb){
        _event.on('play.turn',cb);
    };

    that.onOtherPlay = function(cb){
        _event.on('play.otherPlay',cb);
    };

    return that;
};
export default SocketController;