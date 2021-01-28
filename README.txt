# Code Observation
The code is writting really good, proper/meaningful function names with proper/meaningfull variable but i think it needs some code cleanup, lack of comments about code, variable naming should follow one standard, some of the variables is in camel case and some of them are in snack case.
# Code Refactoring Details
- BookingRepository.php : Refactored imports into more simple and group format
- BookingController.php : Refactored distanceFeed function, the code was a little bit messy, little bit cleanup and proper alignment improve readability
- BookingController.php : Removed $request->all() from getPotentialJobs function, because that was not used in function.
- BookingController.php : Refactored endJob and customerNotCall functions.
- BookingRepository.php : Rearranged getUsersJobsHistory function into more readable form.
- BookingRepository.php : Refactored storeJobEmail function.
- BookingRepository.php : Refactored jobToData function, it would be better to have some transform method for it
- BookingRepository.php : Refactored changeCompletedStatus function. removed unnecessary return statement.
- BookingRepository.php : Refactored changeStartedStatus function. removed unnecessary return statement.