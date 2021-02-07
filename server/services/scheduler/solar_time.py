from math import sin, cos, tan, asin, acos, radians, degrees, trunc, sqrt, atan, floor
import datetime

def GetDays(dat):
    y = datetime.datetime.now().year
    fe = datetime.datetime(y, 12, 31).timestamp()
    return (dat * 24 * 3600 - fe) / (24 * 3600)
    
def Adjust(Value, Bounds):
    while Value >= Bounds:
        Value = Value - Bounds
    while Value < 0:
        Value = Value + Bounds
    return Value

def calc_tzone(date):
    hh = date * 24 * 3600
    local = datetime.datetime.fromtimestamp(hh)
    utc = datetime.datetime.utcfromtimestamp(hh)
    d = local.hour - utc.hour
    if d < 0:
        d = 24 + d
    return d

def GetSunTime(Dt, Latitude, Longitude, Zenith, LocalOffset, SunTime):
    # 1. first calculate the day of the year
    N = trunc(GetDays(Dt))
    
    # 2. convert the longitude to hour value and calculate an approximate time

    LngHour = Longitude / 15
    
    if SunTime == "Sunrise":
        t = N + ((6 - LngHour) / 24)
    else:
        t = N + ((18 - LngHour) / 24)

    # 3. calculate the Sun's mean anomaly

    M = (0.9856 * t) - 3.289

    #4. calculate the Sun's true longitude
    
    L = M + (1.916 * sin(radians(M))) + (0.020 * sin(radians(2 * M))) + 282.634
    # NOTE: L potentially needs to be adjusted into the range [0,360) by adding/subtracting 360
    L = Adjust(L, 360)
 
    # 5a. calculate the Sun's right ascension
    
    RA = degrees(atan(0.91764 * tan(radians(L))))
    # NOTE: RA potentially needs to be adjusted into the range [0,360) by adding/subtracting 360
    RA = Adjust(RA, 360)
    
    # 5b. right ascension value needs to be in the same quadrant as L
    
    Lquadrant = floor(L / 90) * 90
    RAquadrant = floor(RA / 90) * 90
    RA = RA + (Lquadrant - RAquadrant)

    # 5c. right ascension value needs to be converted into hours

    RA = RA / 15
    
    # 6. calculate the Sun's declination

    sinDec = 0.39782 * sin(radians(L))
    cosDec = cos(asin(sinDec))

    # 7a. calculate the Sun's local hour angle

    HCos = (cos(radians(Zenith)) - (sinDec * sin(radians(Latitude)))) / (cosDec * cos(radians(Latitude)))
    if (HCos > 1) or (HCos < -1):
        return

    # 7b. finish calculating H and convert into hours

    if SunTime == "Sunrise":
        H = 360 - degrees(acos(HCos))
    else:
        H = degrees(acos(HCos))
        
    H = H / 15
    # 8. calculate local mean time of rising/setting
    LocalT = H + RA - (0.06571 * t) - 6.622

    # 9. adjust back to UTC

    UT = LocalT - LngHour
    # NOTE: UT potentially needs to be adjusted into the range [0,24) by adding/subtracting 24
    UT = Adjust(UT, 24)
    # ----------
    # 10. convert UT value to local time zone of latitude/longitude
    Result = UT + calc_tzone(Dt) #LocalOffset
    Result = Adjust(Result, 24)
    return Result


def calc_time(date, t):
    h = trunc(t)
    m = round((t - h) * 60)
    res = datetime.datetime.now()
    d = datetime.datetime(date.year, date.month, date.day).timestamp()
    res = datetime.datetime.fromtimestamp(d + h * 3600 + m * 60)
    return res

def calc_test():
    now = datetime.datetime.utcnow()
    date = datetime.datetime(now.year, now.month, now.day).timestamp() // (24 * 3600)
    lat, long = 49.697287, 34.354388
    off = 0
    t1 = GetSunTime(date, lat, long, 90.8333333333333, off, "Sunrise")
    t2 = GetSunTime(date, lat, long, 90.8333333333333, off, "Sunset")

    print(calc_time(now, t1))
    print(calc_time(now, t2))

#calc_test()
