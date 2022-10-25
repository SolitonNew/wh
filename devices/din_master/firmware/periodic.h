/*

    Part of the Watch House system     
    https://github.com/SolitonNew/wh
    
    Author: Moklyak Alexandr
  
*/

#define PERIODIC_STEP_INTERVAL 5000 // usec
#define PERIODIC_STEP_MAX PERIODIC_STEP_INTERVAL/MAIN_LOOP_DELAY

void periodic_processing(void);