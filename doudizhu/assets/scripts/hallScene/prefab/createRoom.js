import global from '../global';
cc.Class({
    extends: cc.Component,

    properties: {
        // foo: {
        //     // ATTRIBUTES:
        //     default: null,        // The default value will be used only when the component attaching
        //                           // to a node for the first time
        //     type: cc.SpriteFrame, // optional, default is typeof default
        //     serializable: true,   // optional, default is true
        // },
        // bar: {
        //     get () {
        //         return this._bar;
        //     },
        //     set (value) {
        //         this._bar = value;
        //     }
        // },
    },

    // LIFE-CYCLE CALLBACKS:

    // onLoad () {},

    start () {

    },

    onButtonClick:function(event,customData)
    {
        if([1,2,3,4].indexOf(customData))
        {
            global.socket.requestCreateRoom({
                id:global.playerData.id,
                times:customData,
                token:global.playerData.token
            },(error,data)=>{
                if(!error)
                {
                    global.roomData.id = data.room.id;
                    global.roomData.p1 = JSON.parse(data.room.p1);
                    global.roomData.times = data.room.times;
                    global.roomData.bottom = data.room.bottom;
                    global.roomData.state = data.room.state;
                    cc.director.loadScene('gameScene');
                }
                else
                    cc.log(error);
            });
        }
        this.node.destroy();
    },

    // update (dt) {},
});
