USE LandingPageDB;

-- To seed a custom icon:
--   1. Place the image file in app/uploads/icons/ (e.g. compass.png)
--   2. Set icon_path to '/uploads/icons/filename.png'
--   3. If icon_path is NULL, the site auto-fetches the favicon from the website

-- ── Home Grid Links ──
INSERT INTO def_links (name, url, icon_path) VALUES
('Compass',           'https://coodanup-wa.compass.education/',                                                         NULL),
('Connect',           'https://connect.det.wa.edu.au/',                                                                 NULL),
('DAM',               'https://apps.det.wa.edu.au/dam/',                                                                NULL),
('Issue Tracker',     'https://coodanup-wa.compass.education/Organise/IssueTracker/',                                   NULL),
('HRMIS',             'https://hrmis.det.wa.edu.au/psp/HRPRD/?cmd=login&languageCd=ENG&',                               NULL),
('OneDrive',          'https://educationwaeduau-my.sharepoint.com/',                                                    NULL),
('SCSA',              'https://www.scsa.wa.edu.au/',                                                                    NULL),
('IKON',              'https://ikon.education.wa.edu.au/',                                                              NULL),
('RTP',               'https://apps.det.wa.edu.au/irt/',                                                                NULL),
('Teams',             'https://teams.microsoft.com/v2/',                                                                NULL),
('TV 4 ED',           'https://coodanup.functionalsolutions.com.au/SmartLibraryWeb/SmartLibraryPageLoader.aspx?PageName=LOGIN_FULL', NULL),
('YouTube',           'https://www.youtube.com/',                                                                       NULL),
('Canva',             'https://www.canva.com/',                                                                         NULL),
('Chronicle',         'https://coodanup-wa.compass.education/Organise/Chronicle/',                                      NULL),
('Attendance',        'https://educationwaeduau.sharepoint.com/:x:/r/teams/4148.AllStaff.Team/Shared%20Documents/Conditions%20for%20Learning/Attendance/Attendance%20Tracker/2025%20S2%20Attendance%20Tracker%20v3.xlsx?d=wefdd192b46d74d9c9573fa2c13fbc35f&csf=1&web=1&e=iHcIPm', NULL),
('PBIS',              'https://app.pbisrewards.com/login.php',                                                          NULL),
('Office 365',        'https://www.office.com/apps?auth=2&home=1',                                                      NULL),
('Elastik',           'https://au.elastik.com/login',                                                                   NULL),
('Careers Hub',       'https://coodanupcollege.careertools.com.au/',                                                    NULL),
('CSC SSH',           'https://educationwa.service-now.com/ict',                                                        NULL),
('Resource Booking',  'https://coodanup-wa.compass.education/Organise/ResourceBooking/',                                NULL),
('RAMS',              'https://admin-det-wagov.bigredsky.com/',                                                         NULL),
('QuickCliq',         'https://app.quickcliq.com.au/',                                                                  NULL),

-- ── Sidebar Links ──
('Emails',            'https://outlook.office.com/mail/',                                                               NULL),
('Calendar',          'https://coodanup-wa.compass.education/Organise/Calendar/',                                       NULL),

-- ── SharePoint Sites ──
('SP - All Staff',        'https://educationwaeduau.sharepoint.com/teams/4148.AllStaff.Team/Shared%20Documents/Forms/AllItems.aspx',                       NULL),
('SP - Admin',            'https://educationwaeduau.sharepoint.com/teams/CoodanupCollege-Administration4148/Shared%20Documents/Forms/AllItems.aspx',       NULL),
('SP - Executive',        'https://educationwaeduau.sharepoint.com/teams/CoodanupCollege-ExecutiveTeam4148/Shared%20Documents/Forms/AllItems.aspx',        NULL),
('SP - SAER',             'https://educationwaeduau.sharepoint.com/teams/CoodanupCollege-SAER4148/Shared%20Documents/Forms/AllItems.aspx',                 NULL),
('SP - Student Services', 'https://educationwaeduau.sharepoint.com/teams/CoodanupCollege-StudentServices4148/Shared%20Documents/Forms/AllItems.aspx',      NULL),
('SP - Staff Services',   'https://educationwaeduau.sharepoint.com/teams/CoodanupCollege-StaffServices4148/Shared%20Documents/Forms/AllItems.aspx',        NULL),
('SP - SLPA',             'https://educationwaeduau.sharepoint.com/teams/CoodanupCollege-SLPA4148/Shared%20Documents/Forms/AllItems.aspx',                 NULL);


-- ── Themes ──
INSERT INTO themes (name, colour1, colour2, colour3, primary_text_colour, secondary_text_colour, highlight_colour) VALUES
('Midnight Neon',        0x141B2D, 0x1A2236, 0x27304A, 0xE5E7EB, 0x9CA3AF, 0x22D3EE),
('Warm Minimal',         0xFAFAF9, 0xF5F5F4, 0xE7E5E4, 0x1C1917, 0x57534E, 0xF97316),
('Forest Calm',          0x143326, 0x1F5A3E, 0x3A7F63, 0xD8F3DC, 0x95D5B2, 0xFFD166),
('Cyberpunk Pop',        0x08080F, 0x121226, 0x1B1B36, 0xEAEAFF, 0xB8B8FF, 0xFF2EC4),
('Ocean Breeze',         0xCFEAF8, 0xA6D8F0, 0x6FC3EA, 0x0C4A6E, 0x075985, 0x0EA5E9),
('Coffee House',         0x2B1D14, 0x3E2A1E, 0x5A3E2B, 0xFAF3E0, 0xD6C4A8, 0xE09F3E),
('Modern Tech',          0x020617, 0x030712, 0x111827, 0xF9FAFB, 0x9CA3AF, 0x6366F1),
('Pastel UI',            0xF6CED3, 0xD8E6E2, 0xC9CEFA, 0x3A3A3A, 0x5C5C5C, 0x8B5CF6),
('Desert Sunset',        0x3D1F1F, 0x5A2A27, 0x7C2D12, 0xFFECD1, 0xFCD5B5, 0xFB8500),
('Clean Corporate',      0xF8FAFC, 0xE8EEF5, 0xD4DEE9, 0x0F172A, 0x475569, 0x2563EB),
('Retro Terminal',       0x020617, 0x030712, 0x022C22, 0xA7F3D0, 0x6EE7B7, 0x22C55E),
('Luxury Dark',          0x121212, 0x1A1A1A, 0x262626, 0xF5F5F5, 0xB3B3B3, 0xD4AF37),
('Soft Nature',          0xF0FDF4, 0xDCFCE7, 0xBBF7D0, 0x14532D, 0x166534, 0x4ADE80),
('Bold Editorial',       0x1F2937, 0x374151, 0x4B5563, 0xF9FAFB, 0xD1D5DB, 0xEF4444),
('Playful Gradient',     0x4F7DF3, 0x6B6FE8, 0x8F6FEF, 0xFFFFFF, 0xE0E7FF, 0xFACC15);