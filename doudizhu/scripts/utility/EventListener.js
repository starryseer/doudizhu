const EventListener = function(obj){
    let Register = {};
    obj.on = function(type,method){
        if(Register.hasOwnProperty(type))
        {
            Register[type].push(method);
        }
        else
        {
            Register[type] = [method]
        }
    }
    obj.fire = function(type)
    {
 
        if(Register.hasOwnProperty(type))
        {
            let handlerList = Register[type];
            for(let i = 0;i<handlerList.length;i++)
            {
                let handler = handlerList[i];
                let args = [];
                for(let j =0;j<arguments.length;j++)
                {
                    args.push(arguments[j]);
                }
                handler.call(this,args);
            }
        }
    }
    obj.removeListener = function(type){
        Register[type] = [];
    }
    obj.removeAllListeners = function(){
        Register = {};
    }
    return obj;
};
export default EventListener;