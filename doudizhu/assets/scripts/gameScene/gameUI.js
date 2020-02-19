import global from "../global";

cc.Class({
    extends: cc.Component,

    properties: {
        cardPrefab:cc.Prefab,
        bottom:cc.Node,
        robUI:cc.Node,
        playUI:cc.Node,
    },

    // LIFE-CYCLE CALLBACKS:

    onLoad () {
        this.robUI.active = false;
        this.playUI.active = false;
        this.node.on('pushBottom',function(){
            var card = null;
            for(var i=0;i<3;i++)
            {
                card = cc.instantiate(this.cardPrefab);
                card.parent = this.bottom;
                card.scale = 0.5;
                card.setPosition(card.width*0.6*(i-1),0);
            }

        }.bind(this));

        this.node.on('robTurn',function(data){
            this.robUI.active = true;
        }.bind(this));

        this.node.on('showBottom',function(bottom){
            var cards = this.bottom.children;
            for(var i =0;i<cards.length;i++)
            {
                cards[i].emit('init',bottom[i]);
            }
            this.bottom.runAction(cc.sequence(
                cc.delayTime(1),
                cc.spawn(cc.moveTo(1,cc.v2(0,250)),cc.scaleTo(1,0.6,0.6))
                )
            );
        }.bind(this));

        this.node.on('playTurn',function(){
            this.playUI.active = true;
        }.bind(this));
    },

    start () {

    },

    onButtonClick:function(target,customData){
        global.socket.requestRobState({
            id:global.playerData.id,
            token:global.playerData.token,
            roomId:global.roomData.id,
            rob:customData
        },
        (error,data)=>{
            if(!error)
            {
                this.robUI.active = false;
            }
            else
                cc.log(error);
        });
    },

    onPlayCardClick:function(target,customData)
    {
        if(global.gameData.playCards.length == 0)
            return ;
        global.socket.requestPlayCard({
            id:global.playerData.id,
            token:global.playerData.token,
            roomId:global.roomData.id,
            push:customData,
            card:global.gameData.playCards
        },
        (error,data)=>{
            if(!error)
            {
                this.playUI.active = false;
            }
            else
                cc.log(error);
        });
    }

    // update (dt) {},
});
