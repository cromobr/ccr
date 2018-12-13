;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;
; MG2/kh_mod Blacklist for comments
; Structure
; - First entry:  Search string or Regular Expressions (PCRE)
; - Second entry: Search item
;                         1...Name
;                         2...E-Mail
;                         4...Comment
;                         8...IP
;                        16...Host
; - Third entry:  Type of search string (optional)
;                         0...String
;                         1...Regexp (PCRE)
;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;

; Entry seeks 'sex' in Name and Comment (case insensitive)
/sex/i, 5, 1

; Entry seeks 'casino' in Name (case insensitive)
casino, 1
