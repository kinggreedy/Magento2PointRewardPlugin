# Magento 2 - Point Reward module

A demonstration of writing Magento 2 (v 2.2.1) custom module       
The module will add a new reward system, every products will have a point. A customer will earn points when ordering items and receive discount based on their current collected point.   

# Setup

Clone the repo to `app/code/Magento`    
Run `php bin/magento setup:upgrade` and `php bin/magento pointreward:init`

# Screenshot

1. Customer discount level and collected points        
![customer total point](/screenshots/s0.png?raw=true)    

2. Product reward attributes    
![product reward attributes](/screenshots/s1.png?raw=true)    

3. MiniCart layout     
![minicart layout](/screenshots/s2.png?raw=true)    

4. Summary layout (with 5% discount)    
![summary layout](/screenshots/s3.png?raw=true)    
