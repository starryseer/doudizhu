import global from './global'
cc.Class({
    extends: cc.Component,

    properties: {
       nicknameLabel:cc.Label,
       IDLabel:cc.Label,
       fenshuLabel:cc.Label,
       avatarUrl:cc.Sprite,
       createRoomPrefab:cc.Prefab,
       joinRoomPrefab:cc.Prefab,
    },

    // LIFE-CYCLE CALLBACKS:

    onLoad () {
        this.nicknameLabel.string = global.playerData.nickName;
        this.IDLabel.string = "ID: "+ global.playerData.id;
        this.fenshuLabel.string = String(global.playerData.gold);
        cc.loader.load({ url: global.playerData.avatarUrl, type: 'jpg' }, (error, purl) => {

            let oldSize = this.avatarUrl.node.width;
            this.avatarUrl.spriteFrame = new cc.SpriteFrame(purl)
            let newSize = this.avatarUrl.node.width;
            this.avatarUrl.node.scale = oldSize/newSize;
        });
    },

    start () {

    },

    onButtonClick:function(event,customData){
        switch(customData){
            case 'join_room':
                let joinRoom = cc.instantiate(this.joinRoomPrefab);
                joinRoom.parent = this.node;
                break;
            case 'create_room':
                let createRoom = cc.instantiate(this.createRoomPrefab);
                createRoom.parent = this.node;
                break;
            default:
                break;
        }
    },

    // update (dt) {},
});
