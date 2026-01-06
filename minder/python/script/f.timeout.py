import sys
import signal
 
class TimeoutException(Exception): 
    pass 
 
def timeout(timeout_time, default):
    def timeout_function(f):
        def f2(*args):
            def timeout_handler(signum, frame):
                raise TimeoutException()
 
            old_handler = signal.signal(signal.SIGALRM, timeout_handler) 
            signal.alarm(timeout_time) # triger alarm in timeout_time seconds
            try: 
                retval = f()
            except TimeoutException:
                return default
            finally:
                signal.signal(signal.SIGALRM, old_handler) 
            signal.alarm(0)
            return retval
        return f2
    return timeout_function
 
@timeout(3, "default name")
def get_name():
    print "Please enter a name: ",
    name = sys.stdin.readline()
    return name
 
@timeout(1, "default city")
def get_city():
    print "Please enter a city: ",
    city = sys.stdin.readline()
    return city
 

