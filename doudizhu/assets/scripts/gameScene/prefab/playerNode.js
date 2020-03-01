import global from "../../global";

cc.Class({
    extends: cc.Component,

    properties: {
        headImage:cc.Sprite,
        nicknameLab:cc.Label,
        goldLab:cc.Label,
        readyIcon:cc.Node,
        offlineIcon:cc.Node,
        cardParent:cc.Node,
        cardPrefab:cc.Prefab,
        robUI:cc.Node,
        icon:cc.Sprite,
        robFrame:cc.SpriteFrame,
        noRobFrame:cc.SpriteFrame,
        lordFrame:cc.SpriteFrame,
        cardPlayNode:cc.Node,
        noPushLab:cc.Prefab,
    },

    ctor:function(){
        this.p = 0;
        this.id = 0;
    },

    // LIFE-CYCLE CALLBACKS:

    // onLoad () {},

    start () {

    },

    onLoad(){
        this.readyIcon.active = false;
        this.offlineIcon.active = false;
    },

    initData:function(player,pos){
        this.id = player.id;
        this.p = player.p;
        this.nicknameLab.string = player.nickname;
        this.goldLab.string = player.gold;
        this.robUI.active = false;
        if(player.ready == 1)
            this.readyIcon.active = true;
        cc.loader.load({ url: player.avatarUrl, type: 'jpg' }, (error, purl) => {
            let oldSize = this.headImage.node.width;
            this.headImage.spriteFrame = new cc.SpriteFrame(purl)
            let newSize = this.headImage.node.width;
            this.headImage.node.scale = oldSize/newSize;
        });
        this.node.setPosition(pos[0]);
        this.cardParent.setPosition(pos[1]);
        this.cardPlayNode.setPosition(pos[2]);

        this.node.on('ready',function(user){
            global.roomData['p'+user.p]['ready'] = 1;
            this.readyIcon.active = true;
        }.bind(this));

        this.node.on('start',function(data){
            this.readyIcon.active = false;
        }.bind(this));

        this.node.on('pushCard',function(data){
            var card = null;
            if(this.id == global.playerData.id)
            {
                global.gameData['c'+this.p] = data.card;
                for(var i =0;i<data.card.length;i++)
                {
                    card = cc.instantiate(this.cardPrefab);
                    card.parent = this.cardParent;
                    card.setPosition(card.width*0.4*i,0);
                    card.emit('init',data.card[i]);
                }
            }
            else
            {
                global.gameData['c'+this.p] = (new Array(17)).fill('');
                for(var i =data.card.length -1;i>=0;i--)
                {
                    card = cc.instantiate(this.cardPrefab);
                    card.parent = this.cardParent;
                    card.scale = 0.5;
                    card.angle = 90;
                    card.setPosition(0,card.width*0.1*(11-i));
                }

            }
        }.bind(this));

        this.node.on('robTurn',function(data){
            if(data.user.p == this.p && data.user.id != global.playerData.id)
                this.robUI.active = true;
            else
                this.robUI.active = false;
        }.bind(this));

        this.node.on('robState',function(data){
            if(data.user.p == this.p)
            {
                if(data.rob == 1)
                    this.icon.spriteFrame = this.robFrame;
                else
                    this.icon.spriteFrame = this.noRobFrame;
            }
                
        }.bind(this));

        this.node.on('robEnd',function(data){
            this.icon.spriteFrame = null;
            this.robUI.active = false;
            if(data.lord == this.p)
            {
                this.icon.spriteFrame = this.lordFrame;
                global.gameData.lord = this.p;
                global.gameData.state = 1;
                if(data.lord == global.playerData.p)
                {
                    global.gameData['c'+data.lord] = data.card;
                    this.cardParent.destroyAllChildren();
                    var card = null;
                    for(var i =0;i<data.card.length;i++)
                    {
                        card = cc.instantiate(this.cardPrefab);
                        card.parent = this.cardParent;
                        if(data.bottom.includes(data.card[i]))
                        {
                            card.setPosition(card.width*0.4*i,20);
                            card.runAction(cc.sequence(
                                cc.delayTime(2),
                                cc.moveTo(0.1,cc.v2(card.width*0.4*i,0))
                            ));
                        }
                        else
                            card.setPosition(card.width*0.4*i,0);
                        card.emit('init',data.card[i]);
                    }
                }
                else
                {
                    global.gameData['c'+data.lord].push('','','');
                    for(var i=0;i<data.bottom.length;i++)
                    {
                        card = cc.instantiate(this.cardPrefab);
                        card.parent = this.cardParent;
                        card.scale = 0.5;
                        card.angle = 90;
                        card.setPosition(0,card.width*0.1*(12+i));
                    }

                }
            }
                
        }.bind(this));

        this.node.on('unPlayCard',function(){
            if(this.p == global.playerData.p)
                this.cardPlayNode.destroyAllChildren();
        }.bind(this));

        this.node.on('selfPlayerCard',function(data){
            if(this.id != global.playerData.id)
                return;


            if(data.push == 1)
            {
                var playCards = data.card;
                global.gameData.playCards = [];
                for(var k = 0;k<playCards.length;k++)
                {
                    if(global.gameData['c'+this.p].indexOf(playCards[k]) != -1)
                    {
                        global.gameData['c'+this.p].splice(global.gameData['c'+this.p].indexOf(playCards[k]),1);
                    }
                }
            
                var cards = this.cardParent.children;
                var index = 0;
                for(var i=0;i<cards.length;i++)
                {
                    if(cards[i].getComponent(cards[i].name).flag == 1)
                    {
                        cards[i].runAction(cc.sequence(
                            cc.spawn(cc.moveTo(0.3,cc.v2(350,150)),cc.scaleTo(0.3,0.5,0.5)),
                            cc.callFunc(function(target,j){
                                target.destroy();
                                var card = cc.instantiate(this.cardPrefab);
                                card.parent = this.cardPlayNode;
                                card.scale = 0.5;
                                card.setPosition(cc.v2(playCards.length*card.width*(-0.125)+card.width*0.25*j,0));
                                card.emit('init',playCards[j]);
                                card.emit('untouch',{});
                            }.bind(this),cards[i],index)
                            )
                        );
                        index++;
                    }
                }
    
                var cardList = this.cardParent.children;
                var indexCard = 0;
                var len = global.gameData['c'+this.p].length;
                for(var i=0;i<cardList.length;i++)
                {
                    if(cardList[i] && playCards.indexOf(cardList[i].getComponent('card').value) == -1)
                    {
                        cardList[i].setPosition(cc.v2(cardList[i].width*0.4*9-cardList[i].width*0.2*len+cardList[i].width*0.4*indexCard));
                        indexCard++;
                    }
                }
            }
            else
            {
                var noPushLab = cc.instantiate(this.noPushLab);
                noPushLab.parent = this.cardPlayNode;
            }
            
        
            
        }.bind(this));

        cc.systemEvent.on('resetSelfPlayCard',function(){
            if(this.id != global.playerData.id)
                return;
            var cards = this.cardParent.children;
            for(var i=0;i<cards.length;i++)
            {
                if(cards[i].getComponent('card').flag == 1)
                {
                    cards[i].getComponent('card').flag = 0;
                    cards[i].y-=20;
                }
            }
            global.gameData.playCards = [];
        }.bind(this));

        this.node.on('otherPlay',function(data){
            if(this.p != data.p)
                return;

            this.cardPlayNode.destroyAllChildren();   
            if(data.push == 1)
            {
                var cards = this.cardParent.children;
                var len = cards.length;
                for(var i=len-1;i>(len -1 - data.card.length);i--)
                {
                    cards[i].destroy();
                }
    
                var nextP = (global.playerData.p + 1)%3==0?3:(global.playerData.p + 1)%3;
                if(data.p == nextP)
                {
                    for(var i =0;i<data.card.length;i++)
                    {
                        var card = cc.instantiate(this.cardPrefab);
                        card.parent = this.cardPlayNode;
                        card.scale = 0.5;
                        card.zindex = i;
                        card.setPosition(cc.v2(card.width*0.2*i,0));
                        card.emit('init',data.card[i]);
                        card.emit('untouch',{});
                    }
                }
                else
                {
                    for(var i =0;i<data.card.length;i++)
                    {
                        var card = cc.instantiate(this.cardPrefab);
                        card.parent = this.cardPlayNode;
                        card.scale = 0.5;
                        card.zindex = i;
                        card.setPosition(cc.v2((data.card.length -i -1)*card.width*(-0.2),0));
                        card.emit('init',data.card[i]);
                        card.emit('untouch',{});
                    }
                }
            }
            else
            {
                var noPushLab = cc.instantiate(this.noPushLab);
                noPushLab.parent = this.cardPlayNode;
            }    

        }.bind(this));

        cc.systemEvent.on('tipCard',function(tipCards){
            if(this.id != global.playerData.id)
                return;
            global.gameData.playCards = [];
            var cards = this.cardParent.children;
            for(var i=0;i<cards.length;i++)
            {
                if(tipCards.indexOf(cards[i].getComponent('card').value) != -1)
                {
                    if(cards[i].getComponent('card').flag == 0)
                    {
                        cards[i].y+=20;
                        cards[i].getComponent('card').flag = 1;
                    }
                        
                    global.gameData.playCards.push(cards[i].getComponent('card').value);
                }
                else
                {
                    if(cards[i].getComponent('card').flag == 1)
                    {
                        cards[i].y-=20;
                        cards[i].getComponent('card').flag = 0;
                    }
                }
            }
        }.bind(this));

    }

    // update (dt) {},
});
