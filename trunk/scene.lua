--[[
%% properties
3 Temperature
27 value
28 value
29 value
30 value
49 value
68 value
%% globals
--]]

local debug = 0;
local trigger = fibaro:getSourceTrigger();

if (trigger['type'] == 'property') then
  
  local deviceId = tonumber(trigger['deviceID']);
  
  if (debug == 1) then
    fibaro:debug("Changing temp id=" .. deviceId);
  end
  
  local globalId = fibaro:getGlobalValue("currTempID");
  local isError = "";
  local count = 0;
  
  if (debug == 1) then
  	fibaro:debug(deviceId .. " pre first while : "..globalId);
  end
  
  while ((globalId ~= "-1") and (isError == "")) do
    if (count > 5) then
      isError = "Error";
    else
      fibaro:sleep(1000);
      globalId = fibaro:getGlobalValue("currTempID");
      count = count + 1;
    end
  end
  
  if (debug == 1) then
  	fibaro:debug(deviceId .. " post first while");
  end
  
  if ((isError == "") and (globalId == "-1")) then
    
    fibaro:setGlobal("currTempID", deviceId); 
    globalId = deviceId;
    
    if (debug == 1) then
    	fibaro:debug(deviceId .. " pre second while : " .. globalId);
    end
    
    if (debug == 1) then
      fibaro:debug(deviceId .. " Press start : "..globalId);
    end 
    -- on appui sur le boutton
    fibaro:call(59, "pressButton", 1); 
    
    fibaro:sleep(10*1000);
    
    if (debug == 1) then
      fibaro:debug(deviceId .. " End Press start : "..globalId);
    end
    
    globalId = fibaro:getGlobalValue("currTempID");
    
    if (globalId ~= "-1") then
      
      fibaro:setGlobal("currTempID", "-1"); 
      
      local message = "Error : " .. globalId .. " cannot be handled !";
      fibaro:debug(message);
      
    end

    fibaro:sleep(1000);
    
  else
   if (debug == 1) then
   	fibaro:debug(deviceId .. " Aie err: "..isError.." globalId: " .. globalId);
   end
  end
  
  if (debug == 1) then
  	fibaro:debug(deviceId .. " End");
  end
  
else
  fibaro:debug("Manual start");
end


