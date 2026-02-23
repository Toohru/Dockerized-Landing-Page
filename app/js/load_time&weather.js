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

// WA DOE school term dates.
// Verify each year at: https://www.education.wa.edu.au/school-term-dates
const allTerms = [
    // ── 2025 ──
    { start: new Date(2025, 1,  3), end: new Date(2025, 3, 11), term: 1, year: 2025 }, // T1: Feb 3 – Apr 11
    { start: new Date(2025, 3, 28), end: new Date(2025, 6,  4), term: 2, year: 2025 }, // T2: Apr 28 – Jul 4
    { start: new Date(2025, 6, 21), end: new Date(2025, 8, 26), term: 3, year: 2025 }, // T3: Jul 21 – Sep 26
    { start: new Date(2025, 9, 13), end: new Date(2025, 11,19), term: 4, year: 2025 }, // T4: Oct 13 – Dec 19
    // ── 2026 (verify dates with WA DOE) ──
    { start: new Date(2026, 1,  2), end: new Date(2026, 3,  9), term: 1, year: 2026 }, // T1: Feb 2 – Apr 9
    { start: new Date(2026, 3, 27), end: new Date(2026, 6,  3), term: 2, year: 2026 }, // T2: Apr 27 – Jul 3
    { start: new Date(2026, 6, 20), end: new Date(2026, 8, 25), term: 3, year: 2026 }, // T3: Jul 20 – Sep 25
    { start: new Date(2026, 9, 12), end: new Date(2026, 11,18), term: 4, year: 2026 }, // T4: Oct 12 – Dec 18
];

// Legacy alias used by the Before Term 1 check below
const termDates = {
    term1Start: allTerms[0].start,
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

    // Before first known term
    if (today < termDates.term1Start) {
        const diffTime = termDates.term1Start - today;
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        const diffWeeks = Math.ceil(diffDays / 7);
        return diffWeeks > 1 ? `${diffWeeks} Weeks before Term 1` : `${diffWeeks} Week before Term 1`;
    }

    // Walk through all known terms in order
    for (let i = 0; i < allTerms.length; i++) {
        const t = allTerms[i];

        // Inside this term
        if (today >= t.start && today <= t.end) {
            return `Term ${t.term} Week ${getTermWeek(today, t.start)}`;
        }

        // Between this term and the next
        const next = allTerms[i + 1];
        if (next && today > t.end && today < next.start) {
            const mondayAfterTerm = new Date(t.end);
            mondayAfterTerm.setDate(t.end.getDate() + (8 - t.end.getDay()) % 7);
            return `Holiday Week ${getTermWeek(today, mondayAfterTerm)}`;
        }
    }

    // After all known terms
    return 'School Break';
}

// Set the week display
document.getElementById("week").innerHTML = getCurrentTermAndWeek();
