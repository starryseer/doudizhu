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
        }.bind(this));

        this.node.on(cc.Node.EventType.TOUCH_START,this.touchBegin,this);
    },
    start () {

    },

    touchBegin:function(){
        if(this.flag)
        {
            this.node.y-=20;
            this.flag=0;
        }
        else
        {
            this.node.y+=20;
            this.flag=1;
        }
        cc.systemEvent.emit('playCard',this.flag,this.value);
    }

    // update (dt) {},
});
