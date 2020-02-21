import global from "../../global";
cc.Class({
    extends: cc.Component,

    ctor:function(){
        this.value = '';
        this.flag = 0;
    },

    properties: {
        cardAtlas:cc.SpriteAtlas
    },

    onLoad(){
        this.node.on('init',function(card){
            this.value = card;
            this.node.getComponent(cc.Sprite).spriteFrame = this.cardAtlas.getSpriteFrame(defines.cardFrame[card]);
            this.node.on(cc.Node.EventType.TOUCH_START,this.touchBegin,this);
        }.bind(this));

        this.node.on('untouch',function(){
            this.node.off(cc.Node.EventType.TOUCH_START,this.touchBegin,this);
        }.bind(this));

        
    },
    start () {

    },

    touchBegin:function(){
        if(this.flag)
        {
            this.node.y-=20;
            this.flag=0;
            if(global.gameData.playCards.indexOf(this.value) != -1)
            {
                global.gameData.playCards.splice(global.gameData.playCards.indexOf(this.value),1);
            }
        }
        else
        {
            this.node.y+=20;
            this.flag=1;
            if(global.gameData.playCards.indexOf(this.value) == -1)
            {
                global.gameData.playCards.push(this.value);
            }
        }
    }

    // update (dt) {},
});
