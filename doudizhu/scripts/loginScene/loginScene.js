import global from './../global';
cc.Class({
    extends: cc.Component,

    properties: {
        account :cc.Label,
        password:cc.Label
    },

    onButtonClick:function(event,customData){
        switch(customData){
            case 'wx_login':
                global.socket.requestWxLogin({
                    account:this.account.string,
                    password:this.password.string
                },(error,data)=>{
                    if(!error)
                    {
                        global.playerData.gold = data.user.gold;
                        global.playerData.id = data.user.id;
                        global.playerData.nickName = data.user.nickname;
                        global.playerData.avatarUrl = data.user.avatarUrl;
                        global.playerData.token = data.user.token;
                        cc.director.loadScene('hallScene');
                    }
                    else
                        cc.log(error);
                });
                break;
            default:
                cc.log('default');
                break;
        }
    },

    // LIFE-CYCLE CALLBACKS:

    // onLoad () {},

    start () {
        global.socket.init();
    },

    // update (dt) {},
});
