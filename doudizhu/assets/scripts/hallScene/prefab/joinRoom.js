import global from '../global'
cc.Class({
    extends: cc.Component,

    properties: {
        roomIdLabel:cc.Node
    },

    onLoad(){
        this.roomId = '';
        this.labelList = this.roomIdLabel.children
    },

    start () {

    },

    onButtonClick:function(event,customData){
        switch(customData){
            case 'close':
                this.node.destroy();
                break;
            case 'clear':
                this.roomId = '';
                break;
            case 'back':
                this.roomId = this.roomId.substring(0,this.roomId.length -1);
                break;
            default:
                if(this.roomId.length < 6)
                {
                    this.roomId+=customData+'';
                }
                
                if(this.roomId.length == 6)
                {
                    global.socket.requestJoinRoom({
                        id:global.playerData.id,
                        token:global.playerData.token,
                        roomId:this.roomId
                    },(error,data)=>{
                        global.roomData.id = data.room.id;
                        global.roomData.times = data.room.times;
                        global.roomData.bottom = data.room.bottom;
                        global.roomData.state = data.room.state;
                        if(data.room.hasOwnProperty('p1'))
                            global.roomData.p1 = JSON.parse(data.room.p1);
                        if(data.room.hasOwnProperty('p2'))
                            global.roomData.p2 = JSON.parse(data.room.p2);
                        if(data.room.hasOwnProperty('p3'))
                            global.roomData.p3 = JSON.parse(data.room.p3);
                        cc.log(global.roomData);
                        cc.director.loadScene('gameScene');
                    });
                    this.node.destroy();
                }
                break;
        }
    },

    update (dt) {
        for(let i = 0;i < this.labelList.length;i++)
        {
            this.labelList[i].getComponent(cc.Label).string = ' ';
        }
        for(let i = 0;i < this.roomId.length;i++)
        {
            this.labelList[i].getComponent(cc.Label).string =this.roomId[i];
        }
    },
});
