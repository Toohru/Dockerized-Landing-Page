var starts = [];
var names = [];
var times = [];

times[0]  = '06:00'; starts[0]  = 21600; names[0]  = 'Start of Day';

times[1]  = '08:35'; starts[1]  = 30900; names[1]  = 'Period 1';

times[2]  = '09:39'; starts[2]  = 34740; names[2]  = 'Period 2';

times[3]  = '10:43'; starts[3]  = 38640; names[3]  = 'Recess';

times[4]  = '11:08'; starts[4]  = 40080; names[4]  = 'Period 3';

times[5]  = '12:12'; starts[5]  = 43920; names[5]  = 'Period 4';

times[6]  = '13:16'; starts[6]  = 47760; names[6]  = 'Lunch';

times[7]  = '13:41'; starts[7]  = 49260; names[7]  = 'Period 5';

times[8]  = '14:45'; starts[8]  = 53100; names[8]  = 'End of Day';

var days = ["Sun", "Mond", "Tue", "Wed", "Thu", "Fri", "Sat"];
var months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
var randm = false;
var adjustseconds = 0;

function timeadj() {
    if ((_('timeadj').value > -300) & (_('timeadj').value < 300)) {
        adjustseconds = _('timeadj').value;
        _('timeadj').style.backgroundColor = 'white';
    }
}

function _(el) {
    return document.getElementById(el);
}
setInterval(showclock, 1000);

function showclock() {
    var curperiod = -1;
    var og = new Date();
    var d = new Date(og.getTime() + (adjustseconds * 1000));

    var fdate = d.getDate();
    if (fdate < 10) {
        fdate = '0' + fdate;
    }
    var fhours = d.getHours();
    var ampm = fhours >= 12 ? 'PM' : 'AM';
    fhours = fhours % 12;
    if (fhours === 0) {
        fhours = 12;
    }
    if (fhours < 10) {
        fhours = '0' + fhours;
    }
    var fminutes = d.getMinutes();
    if (fminutes < 10) {
        fminutes = '0' + fminutes;
    }
    var fseconds = d.getSeconds();
    if ((randm) && (fseconds === 0)) {
        colorizerandom();
    }
    if (fseconds < 10) {
        fseconds = '0' + fseconds;
    }
    var dd = days[d.getDay()] + ' ' + months[d.getMonth()] + ' ' + fdate + ' ' + d.getFullYear();
    _("currentdd").innerHTML = dd;
    var tt = fhours + ':' + fminutes + ' ' + ampm;
    _("currenttt").innerHTML = tt;
    cursecs = ((d.getHours()) * 3600) + ((d.getMinutes()) * 60) + (d.getSeconds());
    if (typeof starts !== "undefined") {
        perc = starts.length;
        var now = new Date();
        var day = now.getDay();
        var isWeekend = day === 0 || day === 6; // 0 = Sunday, 6 = Saturday
        
        // Calculate time until next school day (6 AM)
        var hours = now.getHours();
        var minutes = now.getMinutes();
        var seconds = now.getSeconds();
        var totalSecondsToday = (hours * 3600) + (minutes * 60) + seconds;
        var secondsInDay = 24 * 60 * 60;
        var secondsUntilMidnight = secondsInDay - totalSecondsToday;
        
        // Calculate days until next school day (Monday if weekend, tomorrow if Friday, today if school day)
        var daysUntilSchoolDay = 0;
        if (isWeekend) {
            daysUntilSchoolDay = day === 0 ? 1 : 2; // Monday is 1 day after Sunday, 2 days after Saturday
        } else if (day === 5 && cursecs > starts[perc - 1]) { // Friday after school
            daysUntilSchoolDay = 3; // Monday is 3 days away
        } else if (cursecs > starts[perc - 1]) { // School day but after school
            daysUntilSchoolDay = 1; // Next school day is tomorrow
        }
        
        // Calculate total seconds until next school day 6 AM
        var secondsUntilNextSchoolDay = secondsUntilMidnight + 
                                     ((daysUntilSchoolDay - 1) * 24 * 60 * 60) + 
                                     (6 * 60 * 60); // 6 AM
        
        if (cursecs < starts[0]) {
            // Before school starts
            _("wearenow").textContent = secondstohhmmss(Math.abs(cursecs - starts[0]));
            _("wearenowlabel").textContent = `Until ${names[0]}`;
            _("countdown").textContent = secondstohhmmss(secondsUntilNextSchoolDay);
            _("countdowntolabel").textContent = isWeekend ? 'Until Monday' : 'Until next school day';
        } else if (cursecs > starts[perc - 1] || isWeekend) {
            // After school or weekend
            _("wearenow").textContent = isWeekend ? "It's the weekend!" : "School's out!";
            _("wearenowlabel").textContent = isWeekend ? 'Enjoy your break!' : 'Have a great day!';
            _("countdown").textContent = secondstohhmmss(secondsUntilNextSchoolDay);
            _("countdowntolabel").textContent = isWeekend ? 'Until Monday' : 'Until next school day';
        } else {
            // During school hours
            for (x = 0; x < perc - 1; x++) {
                if ((starts[x] < cursecs) && (starts[x + 1] > cursecs)) {
                    curperiod = x;
                    const weAreNow = _("wearenow");
                    const weAreNowLabel = _("wearenowlabel");
                    const countdown = _("countdown");
                    const countdownToLabel = _("countdowntolabel");
                    
                    if (weAreNow) weAreNow.textContent = secondstohhmmss(cursecs - starts[x]);
                    if (weAreNowLabel) weAreNowLabel.textContent = names[x];
                    if (countdown) countdown.textContent = secondstohhmmss(starts[x + 1] - cursecs);
                    if (countdownToLabel) countdownToLabel.textContent = `Until ${names[x + 1]}`;
                }
            }
        }

        var ts = "<div style='margin-top:30px;display:block;width:100%;text-align:center'><div style='display:inline-block;margin-left:auto;margin-right:auto;text-align:left;width:auto'>Today's Schedule:<br />";
        for (x = 0; x < perc - 1; x++) {
            if (x === curperiod) {
                ts = ts + '<strong>';
            }
            disptime = times[x];
            disptimex = times[x + 1];
            disptimez = times[perc - 1];
            if (disptime.substring(0, 2) < 12) {
                disptime = disptime + ' AM';
            } else {
                hc = disptime.substring(0, 2);
                if (hc > 12) {
                    hc = hc - 12;
                };
                if (hc < 10) {
                    hc = '0' + hc;
                }
                disptime = hc + disptime.substring(2, 5) + ' PM';
            }
            if (disptimex.substring(0, 2) < 12) {
                disptimex = disptimex + ' AM';
            } else {
                hc = disptimex.substring(0, 2);
                if (hc > 12) {
                    hc = hc - 12;
                };
                if (hc < 10) {
                    hc = '0' + hc;
                }
                disptimex = hc + disptimex.substring(2, 5) + ' PM';
            }
            if (disptimez.substring(0, 2) < 12) {
                disptimez = disptimex + ' AM';
            } else {
                hc = disptimez.substring(0, 2);
                if (hc > 12) {
                    hc = hc - 12;
                };
                if (hc < 10) {
                    hc = '0' + hc;
                }
                disptimez = hc + disptimez.substring(2, 5) + ' PM';
            }
            ts = ts + disptime + '-' + disptimex + ' ' + names[x] + "<br >";
            if (x === curperiod) {
                ts = ts + '</strong>';

            }
        }

        ts = ts + disptimez + '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' + names[perc - 1] + '</div></div>';

    }
    delete window.d;
}

function omb(hhmm) {
    var ch = hhmm.substr(0, 2);
    var cm = hhmm.substr(3, 2);
    if (ch === 0) {
        ch = '00';
    }
    if (cm === 0) {
        cm = '00';
    }
    return ch + ':' + cm;
}

function secondstohhmmss(secs) {
    var h = parseInt(secs / 3600);
    if (h < 10) {
        h = '0' + h;
    }
    if (h === 0) {
        h = '00';
    }

    var secs = (secs - (h * 3600));
    var m = parseInt(secs / 60);
    if (m < 10) {
        m = '0' + m;
    }
    if (m === 0) {
        m = '00';
    }
    var secs = parseInt(secs - (m * 60));

    var s = secs;
    if (s < 10) {
        s = '0' + s;
    }
    if (h !== '00') {
        return h + ':' + m;
    } else if (m !== '00') {
        return m + 'm';
    } else if (m == '00') {
        return s + 's';
    }
}


// Week calculator with specific term start dates

// Define term start dates for 2025
const termDates = {
    term1Start: new Date(2025, 1, 3), // February 3, 2025 (Monday)
    term1End: new Date(2025, 3, 11), // April 11, 2025 (Friday)
    term2Start: new Date(2025, 3, 28), // April 28, 2025 (Monday)
    term2End: new Date(2025, 6, 4), // July 4, 2025 (Friday)
    term3Start: new Date(2025, 6, 21), // July 21, 2025 (Monday)
    term3End: new Date(2025, 8, 26), // September 26, 2025 (Friday)
    term4Start: new Date(2025, 9, 13), // October 13, 2025 (Monday)
    term4End: new Date(2025, 11, 19) // December 19, 2025 (Friday)
};

// Function to get the Monday of the current week
function getMondayOfCurrentWeek(date) {
    const day = date.getDay();
    // If it's Sunday (0), we need to go back 6 days to get to Monday
    // Otherwise, go back (day - 1) days
    const diff = day === 0 ? 6 : day - 1;
    const monday = new Date(date);
    monday.setDate(date.getDate() - diff);
    return monday;
}

// Function to calculate week number within a term
function getTermWeek(today, termStart) {
    // Get the Monday of the current week
    const currentMonday = getMondayOfCurrentWeek(today);

    // Get the Monday of the term start week
    const termStartMonday = getMondayOfCurrentWeek(termStart);

    // Calculate difference in milliseconds
    const diffTime = currentMonday - termStartMonday;

    // Convert to days and then to weeks
    const diffDays = diffTime / (1000 * 60 * 60 * 24);
    const diffWeeks = Math.floor(diffDays / 7) + 1; // Add 1 because we want to start from Week 1

    return diffWeeks;
}

// Function to determine the current term and week
function getCurrentTermAndWeek() {
    const today = new Date();

    // Before Term 1
    if (today < termDates.term1Start) {
        const diffTime = termDates.term1Start - today;
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        const diffWeeks = Math.ceil(diffDays / 7);

        if (diffWeeks > 1) {
            return `${diffWeeks} Weeks before Term 1`;
        } else {
            return `${diffWeeks} Week before Term 1`;
        }
    }

    // Term 1
    if (today >= termDates.term1Start && today <= termDates.term1End) {
        const weekNumber = getTermWeek(today, termDates.term1Start);
        return `Term 1 Week ${weekNumber}`;
    }

    // Holiday 1
    if (today > termDates.term1End && today < termDates.term2Start) {
        const mondayAfterTerm1 = new Date(termDates.term1End);
        mondayAfterTerm1.setDate(mondayAfterTerm1.getDate() + (8 - mondayAfterTerm1.getDay()) % 7);
        const weekNumber = getTermWeek(today, mondayAfterTerm1);
        return `Holiday Week ${weekNumber}`;
    }

    // Term 2
    if (today >= termDates.term2Start && today <= termDates.term2End) {
        const weekNumber = getTermWeek(today, termDates.term2Start);
        return `Term 2 Week ${weekNumber}`;
    }

    // Holiday 2
    if (today > termDates.term2End && today < termDates.term3Start) {
        const mondayAfterTerm2 = new Date(termDates.term2End);
        mondayAfterTerm2.setDate(mondayAfterTerm2.getDate() + (8 - mondayAfterTerm2.getDay()) % 7);
        const weekNumber = getTermWeek(today, mondayAfterTerm2);
        return `Holiday Week ${weekNumber}`;
    }

    // Term 3
    if (today >= termDates.term3Start && today <= termDates.term3End) {
        const weekNumber = getTermWeek(today, termDates.term3Start);
        return `Term 3 Week ${weekNumber}`;
    }

    // Holiday 3
    if (today > termDates.term3End && today < termDates.term4Start) {
        const mondayAfterTerm3 = new Date(termDates.term3End);
        mondayAfterTerm3.setDate(mondayAfterTerm3.getDate() + (8 - mondayAfterTerm3.getDay()) % 7);
        const weekNumber = getTermWeek(today, mondayAfterTerm3);
        return `Holiday Week ${weekNumber}`;
    }

    // Term 4
    if (today >= termDates.term4Start && today <= termDates.term4End) {
        const weekNumber = getTermWeek(today, termDates.term4Start);
        return `Term 4 Week ${weekNumber}`;
    }

    // After Term 4
    return 'School Break';
}

// Set the week display
document.getElementById("week").innerHTML = getCurrentTermAndWeek();

//weather stuff
const api = 'ebcc9eaa5ccd7c47086f57db63a66d4a';

const iconImg = document.getElementById('weather-icon');
const tempC = document.querySelector('.c');
const desc = document.querySelector('.desc');

window.addEventListener('load', () => {
    const base = `https://api.openweathermap.org/data/2.5/weather?q=Coodanup&appid=ebcc9eaa5ccd7c47086f57db63a66d4a&units=metric`; // Dont forget to change the API and the location

    // Using fetch to get data
    fetch(base)
        .then((response) => {
            return response.json();
        })
        .then((data) => {
            const { temp, feels_like } = data.main;
            const { description, icon } = data.weather[0];
            const { sunrise, sunset } = data.sys;

            const iconUrl = `http://openweathermap.org/img/wn/${icon}@2x.png`;
            const fahrenheit = (temp * 9) / 5 + 32;

            // Converting Epoch(Unix) time to GMT
            const sunriseGMT = new Date(sunrise * 1000);
            const sunsetGMT = new Date(sunset * 1000);

            // Interacting with DOM to show data
            iconImg.src = iconUrl;
            desc.textContent = `${description}`;
            tempC.textContent = `${temp.toFixed(2)} °C`;
        });
});