const gameData = function(){
    let that = {};
    that.c1 = null;
    that.c2 = null;
    that.c3 = null;
    that.bottom = null;
    that.state = 0;
    that.lord = null;
    that.playCards = [];
    that.tipCard = [];
    that.tipIndex = 0;
    return that;
};
export default gameData;