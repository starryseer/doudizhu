import global from '../global';
cc.Class({
    extends: cc.Component,

    properties: {
        readyButton:cc.Node
    },

    // LIFE-CYCLE CALLBACKS:

    // onLoad () {},

    start () {

    },
    onButtonClick:function(event, customEventData)
    {
        switch(customEventData)
        {
            case 'ready':
                global.socket.requestReady(
                    {
                        token:global.playerData.token,
                        id:global.playerData.id,
                        roomId:global.roomData.id,     
                    },(error,data)=>{
                        this.readyButton.active =false;
                        global.roomData['p'+data.user.p].ready = 1;
                    });
                break;
        }
    },
    // update (dt) {},
});
