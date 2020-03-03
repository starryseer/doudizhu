import global from "./../global"
cc.Class({
    extends: cc.Component,

    properties: {
        winFrame:cc.Node,
        loseFrame:cc.Node,

    },

    // LIFE-CYCLE CALLBACKS:

    onLoad () {

        this.winFrame.active = false;
        this.loseFrame.active = false;

        global.socket.onGameEnd((data)=>{
            if(data[1].win)
            {
                this.winFrame.active = true;
            }
            else
            {
                this.loseFrame.active = true;
            }
            cc.systemEvent.emit('restartGame',{}); 
        });
    },

    start () {

    },

    onButtonContinue:function(target,data){     
        this.winFrame.active = false;
        this.loseFrame.active = false;
    }

    // update (dt) {},
});
