import global from './../global'
cc.Class({
    extends: cc.Component,

    ctor:function(){
        this.playPos = {};
    },

    properties: {
        idLab:cc.Label,
        bottomLab:cc.Label,
        timesLab:cc.Label,
        playNodePrefab:cc.Prefab,
        playerPos:cc.Node,
        playerParent:cc.Node,
        beforeGameUI:cc.Node,
        gameUI:cc.Node,
        endGameUI:cc.Node
    },

    // LIFE-CYCLE CALLBACKS:

    onLoad () {
        this.idLab.string = "房号："+global.roomData.id;
        this.bottomLab.string = "底分："+global.roomData.bottom;
        this.timesLab.string = "倍数："+global.roomData.times;
        this.initSeat();
        global.socket.onPlayerJoin((data)=>{
            global.roomData['p'+data[1].user.p] = data[1].user;
            this.addPlayer(data[1].user.p);
        });
        global.socket.onPlayerReady((data)=>{
            var playerList = this.playerParent.children;
            for(var i=0;i<playerList.length;i++)
            {
                if(playerList[i].getComponent(playerList[i].name).id == data[1].user.id)
                    playerList[i].emit('ready',data[1].user);
            }
        });

        global.socket.onGameStart((data)=>{
            global.roomData.state = 1;
            global.gameData.playCards = [];
            var playerList = this.playerParent.children;
            for(var i=0;i<playerList.length;i++)
            {
                playerList[i].emit('start',{});
                playerList[i].emit('pushCard',data[1]);
            } 
            this.gameUI.emit('pushBottom',{});
            
        });

        global.socket.onRobTurn((data)=>{
            if(data[1].user.id == global.playerData.id)
                this.gameUI.emit('robTurn',data[1]);

            var playerList = this.playerParent.children;
            for(var i =0;i<playerList.length;i++)
            {
                playerList[i].emit('robTurn',data[1]);
            }    
        });

        global.socket.onRobOtherState((data)=>{
            var playerList = this.playerParent.children;
            for(var i =0;i<playerList.length;i++)
            {
                playerList[i].emit('robState',data[1]);
            }    
        });

        global.socket.onRobEnd((data)=>{
            this.gameUI.emit('showBottom',data[1].bottom);
            var playerList = this.playerParent.children;
            for(var i =0;i<playerList.length;i++)
            {
                playerList[i].emit('robEnd',data[1]);
            }    
        });

        global.socket.onPlayTurn((data)=>{
            global.gameData.tipCard = data[1].tipCard;
            if(global.gameData.tipCard == 0)
                global.gameData.tipIndex = -1;
            else
                global.gameData.tipIndex = 0;
            this.gameUI.emit('playTurn',{});
            var playerList = this.playerParent.children;
            for(var i=0;i<playerList.length;i++)
            {
                playerList[i].emit('unPlayCard',{});
            }
        });

        global.socket.onOtherPlay((data)=>{
            if(data[1].push == 1)
            {
                global.gameData['c'+data[1].p].splice(0,data[1].card.length);
            }
            var playerList = this.playerParent.children;
            for(var i=0;i<playerList.length;i++)
            {
                playerList[i].emit('otherPlay',data[1]);
            }
        });

        cc.systemEvent.on('selfPlayCard',function(data){
            var playerList = this.playerParent.children;
            for(var i=0;i<playerList.length;i++)
            {
                playerList[i].emit('selfPlayerCard',data);
            }
        }.bind(this));

        cc.systemEvent.on('restartGame',function(data){
            this.beforeGameUI.emit('restart',{});
            this.gameUI.emit('restart',{});
            var playerList = this.playerParent.children;
            for(var i=0;i<playerList.length;i++)
            {
                playerList[i].emit('restart',{});
            }
        }.bind(this));
    },

    start () {

    },

    initSeat:function(){
        this.getSeat();
        this.initPos();
        this.createPlayer();
         
    },

    getSeat:function(){
        for(var i =1;i<=3;i++)
        {
            if(global.roomData['p'+i] != null && global.roomData['p'+i]['id'] == global.playerData.id)
            {
                global.playerData.p = global.roomData['p'+i].p;
                break;
            }
        }
    },

    createPlayer:function(){
        for(var i =1;i<=3;i++)
        {
            if(global.roomData['p'+i] != null)
            {
                this.addPlayer(i);
            }
        }
    },

    initPos:function(){
        let playerPosition =  this.playerPos.children;
        switch(global.playerData.p)
        {
            case 1:
                this.playPos[1] = [playerPosition[0].getPosition(),cc.v2(150,0),cc.v2(550,150)];
                this.playPos[2] = [playerPosition[1].getPosition(),cc.v2(150,0),cc.v2(300,0)];
                this.playPos[3] = [playerPosition[2].getPosition(),cc.v2(-150,0),cc.v2(-300,0)];
                break;
            case 2:
                this.playPos[2] = [playerPosition[0].getPosition(),cc.v2(150,0),cc.v2(550,150)];
                this.playPos[3] = [playerPosition[1].getPosition(),cc.v2(150,0),cc.v2(300,0)];
                this.playPos[1] = [playerPosition[2].getPosition(),cc.v2(-150,0),cc.v2(-300,0)];
                break;
            case 3:
                this.playPos[3] = [playerPosition[0].getPosition(),cc.v2(150,0),cc.v2(550,150)];
                this.playPos[1] = [playerPosition[1].getPosition(),cc.v2(150,0),cc.v2(300,0)];
                this.playPos[2] = [playerPosition[2].getPosition(),cc.v2(-150,0),cc.v2(-300,0)];
                break;
        }
    },

    addPlayer:function(p)
    {
        let playerNode = null;
        playerNode = cc.instantiate(this.playNodePrefab);
        playerNode.parent = this.playerParent;
        playerNode.getComponent(playerNode.name).initData(global.roomData['p'+p],this.playPos[p]);
    },


    // update (dt) {},
});
