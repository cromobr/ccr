;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;
; MG2/kh_mod Whitelist for comments
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

; Entry seeks 'firma xyz' in Name and Comment (case insensitive)
/firma.xyz/i, 5, 1
 
; Entry seeks 'freund@eigene_domain.de' in E-Mail (case insensitive)
freund@eigene_domain.de, 2
