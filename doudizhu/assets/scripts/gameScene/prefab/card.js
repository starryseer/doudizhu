cc.Class({
    extends: cc.Component,

    ctor:function(){
        this.value = '';
    },

    properties: {
        cardAtlas:cc.SpriteAtlas
    },

    onLoad(){
        this.node.on('init',function(card){
            this.value = card;
            this.node.getComponent(cc.Sprite).spriteFrame = this.cardAtlas.getSpriteFrame(defines.cardFrame[card]);
        }.bind(this));
    },
    start () {

    },

    // update (dt) {},
});
