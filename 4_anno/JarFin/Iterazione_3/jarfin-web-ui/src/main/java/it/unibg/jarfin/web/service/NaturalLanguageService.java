package it.unibg.jarfin.web.service;

import java.math.BigDecimal;
import java.time.LocalDate;
import java.util.HashMap;
import java.util.Map;
import java.util.regex.Matcher;
import java.util.regex.Pattern;

import org.springframework.stereotype.Service;
import org.springframework.util.StringUtils;

import it.unibg.jarfin.web.dto.ParsedTransaction;
import it.unibg.jarfin.web.dto.CommandType;

@Service
public class NaturalLanguageService {

    public static final String RISTORANTE = "Ristorante";
    public static final String BAR_COLAZIONE = "Bar/Colazione";
    public static final String SUPERMERCATO = "Supermercato";
    public static final String TRASPORTI = "Trasporti";
    public static final String BOLLETTE = "Bollette";
    public static final String CASA = "Casa";
    public static final String SVAGO = "Svago";
    public static final String SHOPPING = "Shopping";
    public static final String SALUTE = "Salute";
    public static final String SALUTE_SPORT = "Salute/Sport";
    public static final String CURA_PERSONALE = "Cura Personale";
    public static final String STIPENDIO = "Stipendio";
    public static final String BONIFICO = "Bonifico";
    public static final String ENTRATA = "Entrata";

    private static final Pattern NUMBER_PATTERN = Pattern.compile("(\\d+(?:[.,]\\d+)*)");
    private static final Pattern DELETE_CMD = Pattern.compile("(elimina|cancella|rimuovi|togli)",
            Pattern.CASE_INSENSITIVE);
    private static final Pattern UPDATE_CMD = Pattern.compile("(modifica|cambia|aggiorna|correggi)",
            Pattern.CASE_INSENSITIVE);
    private static final Pattern ID_PATTERN = Pattern.compile("(?:id|numero|codice|transazione)\\s+(\\d+)",
            Pattern.CASE_INSENSITIVE);
    private static final Pattern EXPLICIT_DESC_PATTERN = Pattern.compile("descrizione\\s+(.*)",
            Pattern.CASE_INSENSITIVE);

    private static final Map<String, String> CATEGORY_MAP = new HashMap<>();
    private static final Map<String, String> CATEGORY_DEFAULT_DESC = new HashMap<>();
    private static final Map<String, String> VERB_TO_NOUN = new HashMap<>();

    static {
        VERB_TO_NOUN.put("stipendiato", "stipendio");
        VERB_TO_NOUN.put("regalato", "regalo");
        VERB_TO_NOUN.put("comprato", "acquisto");
        VERB_TO_NOUN.put("mangiato", "pranzo");
        VERB_TO_NOUN.put("bevuto", "bevanda");
        VERB_TO_NOUN.put("viaggiato", "viaggio");
        VERB_TO_NOUN.put("pagato", "pagamento");
        VERB_TO_NOUN.put("speso", "spesa");
        VERB_TO_NOUN.put("ricevuto", "entrata");
        VERB_TO_NOUN.put("accreditato", "accredito");
        VERB_TO_NOUN.put("bonifico", "bonifico");

        CATEGORY_MAP.put("mcdonald", RISTORANTE);
        CATEGORY_MAP.put("burger", RISTORANTE);
        CATEGORY_MAP.put("pizza", RISTORANTE);
        CATEGORY_MAP.put("sushi", RISTORANTE);
        CATEGORY_MAP.put("ristorante", RISTORANTE);
        CATEGORY_MAP.put("trattoria", RISTORANTE);
        CATEGORY_MAP.put("bar", BAR_COLAZIONE);
        CATEGORY_MAP.put("colazione", BAR_COLAZIONE);
        CATEGORY_MAP.put("aperitivo", BAR_COLAZIONE);
        CATEGORY_MAP.put("caffè", BAR_COLAZIONE);
        CATEGORY_MAP.put("esselunga", SUPERMERCATO);
        CATEGORY_MAP.put("coop", SUPERMERCATO);
        CATEGORY_MAP.put("lidl", SUPERMERCATO);
        CATEGORY_MAP.put("conad", SUPERMERCATO);
        CATEGORY_MAP.put("carrefour", SUPERMERCATO);
        CATEGORY_MAP.put("eurospin", SUPERMERCATO);
        CATEGORY_MAP.put("spesa", SUPERMERCATO);
        CATEGORY_MAP.put("supermercato", SUPERMERCATO);
        CATEGORY_MAP.put("alimentari", SUPERMERCATO);
        CATEGORY_MAP.put("ortofrutta", SUPERMERCATO);
        CATEGORY_MAP.put("benzina", TRASPORTI);
        CATEGORY_MAP.put("diesel", TRASPORTI);
        CATEGORY_MAP.put("treno", TRASPORTI);
        CATEGORY_MAP.put("trenitalia", TRASPORTI);
        CATEGORY_MAP.put("italo", TRASPORTI);
        CATEGORY_MAP.put("bus", TRASPORTI);
        CATEGORY_MAP.put("autostrada", TRASPORTI);
        CATEGORY_MAP.put("pedaggio", TRASPORTI);
        CATEGORY_MAP.put("parcheggio", TRASPORTI);
        CATEGORY_MAP.put("uber", TRASPORTI);
        CATEGORY_MAP.put("taxi", TRASPORTI);
        CATEGORY_MAP.put("aereo", TRASPORTI);
        CATEGORY_MAP.put("ryanair", TRASPORTI);
        CATEGORY_MAP.put("luce", BOLLETTE);
        CATEGORY_MAP.put("gas", BOLLETTE);
        CATEGORY_MAP.put("enel", BOLLETTE);
        CATEGORY_MAP.put("a2a", BOLLETTE);
        CATEGORY_MAP.put("internet", BOLLETTE);
        CATEGORY_MAP.put("wifi", BOLLETTE);
        CATEGORY_MAP.put("vodafone", BOLLETTE);
        CATEGORY_MAP.put("tim", BOLLETTE);
        CATEGORY_MAP.put("affitto", CASA);
        CATEGORY_MAP.put("mutuo", CASA);
        CATEGORY_MAP.put("ikea", CASA);
        CATEGORY_MAP.put("leroy", CASA);
        CATEGORY_MAP.put("netflix", SVAGO);
        CATEGORY_MAP.put("spotify", SVAGO);
        CATEGORY_MAP.put("cinema", SVAGO);
        CATEGORY_MAP.put("amazon", SHOPPING);
        CATEGORY_MAP.put("zalando", SHOPPING);
        CATEGORY_MAP.put("vinted", SHOPPING);
        CATEGORY_MAP.put("shein", SHOPPING);
        CATEGORY_MAP.put("zara", SHOPPING);
        CATEGORY_MAP.put("h&m", SHOPPING);
        CATEGORY_MAP.put("palestra", SALUTE_SPORT);
        CATEGORY_MAP.put("padel", SALUTE_SPORT);
        CATEGORY_MAP.put("farmacia", SALUTE);
        CATEGORY_MAP.put("medico", SALUTE);
        CATEGORY_MAP.put("dentista", SALUTE);
        CATEGORY_MAP.put("visita", SALUTE);
        CATEGORY_MAP.put("parrucchiere", CURA_PERSONALE);
        CATEGORY_MAP.put("barbiere", CURA_PERSONALE);
        CATEGORY_MAP.put("estetista", CURA_PERSONALE);
        CATEGORY_MAP.put("trucco", CURA_PERSONALE);
        CATEGORY_MAP.put("makeup", CURA_PERSONALE);
        CATEGORY_MAP.put("manicure", CURA_PERSONALE);
        CATEGORY_MAP.put("pedicure", CURA_PERSONALE);
        CATEGORY_MAP.put("massaggio", CURA_PERSONALE);
        CATEGORY_MAP.put("spa", CURA_PERSONALE);
        CATEGORY_MAP.put("centro estetico", CURA_PERSONALE);
        CATEGORY_MAP.put("salone", CURA_PERSONALE);
        CATEGORY_MAP.put("profumeria", CURA_PERSONALE);
        CATEGORY_MAP.put("cosmetica", CURA_PERSONALE);
        CATEGORY_MAP.put("stipendio", STIPENDIO);
        CATEGORY_MAP.put("bonifico", BONIFICO);
        CATEGORY_MAP.put("rimborso", ENTRATA);
        CATEGORY_MAP.put("regalo", ENTRATA);
        CATEGORY_MAP.put("vendita", ENTRATA);

        CATEGORY_DEFAULT_DESC.put(RISTORANTE, "Pranzo/Cena");
        CATEGORY_DEFAULT_DESC.put(BAR_COLAZIONE, "Caffè");
        CATEGORY_DEFAULT_DESC.put(SUPERMERCATO, "Spesa");
        CATEGORY_DEFAULT_DESC.put(TRASPORTI, "Trasporto");
        CATEGORY_DEFAULT_DESC.put(BOLLETTE, "Utenze");
        CATEGORY_DEFAULT_DESC.put(CASA, "Casa");
        CATEGORY_DEFAULT_DESC.put(SVAGO, "Intrattenimento");
        CATEGORY_DEFAULT_DESC.put(SHOPPING, "Acquisto");
        CATEGORY_DEFAULT_DESC.put(SALUTE, "Salute");
        CATEGORY_DEFAULT_DESC.put(SALUTE_SPORT, "Sport");
        CATEGORY_DEFAULT_DESC.put(CURA_PERSONALE, "Estetica");
        CATEGORY_DEFAULT_DESC.put(STIPENDIO, "Stipendio");
        CATEGORY_DEFAULT_DESC.put(BONIFICO, "Bonifico");
        CATEGORY_DEFAULT_DESC.put(ENTRATA, "Entrata");
    }

    /**
     * Parse a natural language string into a ParsedTransaction.
     * 
     * The parser will try to identify the command type (CREATE, DELETE, UPDATE) and
     * the target ID of the transaction. If the command type is CREATE, then the
     * date of the transaction will be set to the current date.
     * 
     * The parser will also try to identify the type of the transaction (INCOME or
     * EXPENSE)
     * and the category of the transaction.
     * 
     * If the command type is UPDATE, then the parser will try to identify an
     * explicit
     * description of the transaction. If no explicit description is found, then the
     * description will be set to null.
     * 
     * If the command type is CREATE, then the parser will try to identify a smart
     * description of the transaction. The smart description is generated by looking
     * at the keywords in the natural language string and generating a description
     * based
     * on the most relevant keywords.
     * 
     * @param text The natural language string to parse.
     * @return The parsed transaction, or null if the string cannot be parsed.
     */
    public ParsedTransaction parse(String text) {
        if (text == null || text.trim().length() < 2)
            return null;

        String lowerCaseText = text.trim().toLowerCase();

        String[] stopWords = { "stop", "nulla", "niente", "annulla", "chiudi", "jar", "giar", "jarfin", "johnny",
                "gionni" };
        for (String w : stopWords) {
            if (lowerCaseText.equals(w) || lowerCaseText.startsWith(w + " ")) {
                return null;
            }
        }

        String normalizedText = convertVerbsToNouns(text);
        ParsedTransaction result = new ParsedTransaction();

        if (DELETE_CMD.matcher(normalizedText).find()) {
            result.setCommandType(CommandType.DELETE);
            result.setTargetId(extractId(normalizedText));
        } else if (UPDATE_CMD.matcher(normalizedText).find()) {
            result.setCommandType(CommandType.UPDATE);
            result.setTargetId(extractId(normalizedText));
        } else {
            result.setCommandType(CommandType.CREATE);
        }

        if (result.getCommandType() == CommandType.CREATE)
            result.setDate(LocalDate.now());
        else
            result.setDate(null);

        String textForAmount = normalizedText;
        Matcher idMatcher = ID_PATTERN.matcher(normalizedText);
        if (idMatcher.find()) {
            textForAmount = normalizedText.replace(idMatcher.group(0), "");
        }

        BigDecimal parsedAmount = parseItalianNumberWords(textForAmount.toLowerCase());

        if (parsedAmount == null) {
            Matcher matcher = NUMBER_PATTERN.matcher(textForAmount);
            if (matcher.find()) {
                parsedAmount = parseSmartDecimal(matcher.group(1));
            }
        }
        result.setAmount(parsedAmount);

        if (containsAny(lowerCaseText, "ricevuto", "stipendio", "entrata", "guadagnato", "bonifico", "accreditato",
                "accredito", "incassato")) {
            result.setType("INCOME");
        } else if (containsAny(lowerCaseText, "speso", "pagato", "uscita", "perso", "costo")) {
            result.setType("EXPENSE");
        }

        String foundCategory = null;
        String matchedKeyword = null;
        int maxMatchLength = 0;

        for (Map.Entry<String, String> entry : CATEGORY_MAP.entrySet()) {
            if (lowerCaseText.contains(entry.getKey()) && entry.getKey().length() > maxMatchLength) {
                foundCategory = entry.getValue();
                matchedKeyword = entry.getKey();
                maxMatchLength = entry.getKey().length();
            }
        }
        result.setCategory(foundCategory);

        if (result.getCommandType() == CommandType.UPDATE) {
            Matcher descMatcher = EXPLICIT_DESC_PATTERN.matcher(text);
            if (descMatcher.find()) {
                result.setDescription(StringUtils.capitalize(descMatcher.group(1).trim()));
            } else {
                result.setDescription(null);
            }
        } else {
            String description = extractSmartDescription(normalizedText, foundCategory, matchedKeyword);
            result.setDescription(description);
        }

        return result;
    }

    /**
     * Parses a string containing a number in Italian words
     * and returns its equivalent decimal value.
     * 
     * @param text the string to parse
     * @return the parsed decimal value, or null if no valid number was found
     */
    private BigDecimal parseItalianNumberWords(String text) {
        String clean = text.toLowerCase().replaceAll("[^a-z0-9,.]", " ");
        String[] tokens = clean.split("\\s+");

        BigDecimal grandTotal = BigDecimal.ZERO;
        StringBuilder currentBuffer = new StringBuilder();

        Map<String, Long> units = new HashMap<>();
        units.put("uno", 1L);
        units.put("un", 1L);
        units.put("una", 1L);
        units.put("due", 2L);
        units.put("tre", 3L);
        units.put("quattro", 4L);
        units.put("cinque", 5L);
        units.put("sei", 6L);
        units.put("sette", 7L);
        units.put("otto", 8L);
        units.put("nove", 9L);
        units.put("dieci", 10L);
        units.put("undici", 11L);
        units.put("dodici", 12L);
        units.put("tredici", 13L);
        units.put("quattordici", 14L);
        units.put("quindici", 15L);
        units.put("sedici", 16L);
        units.put("diciassette", 17L);
        units.put("diciotto", 18L);
        units.put("diciannove", 19L);

        Map<String, Long> tens = new HashMap<>();
        tens.put("venti", 20L);
        tens.put("vent", 20L);
        tens.put("trenta", 30L);
        tens.put("trent", 30L);
        tens.put("quaranta", 40L);
        tens.put("quarant", 40L);
        tens.put("cinquanta", 50L);
        tens.put("cinquant", 50L);
        tens.put("sessanta", 60L);
        tens.put("sessant", 60L);
        tens.put("settanta", 70L);
        tens.put("settant", 70L);
        tens.put("ottanta", 80L);
        tens.put("ottant", 80L);
        tens.put("novanta", 90L);
        tens.put("novant", 90L);

        boolean foundSomething = false;

        for (int i = 0; i < tokens.length; i++) {
            String t = tokens[i];
            if (t.isEmpty())
                continue;

            if (t.matches("euro|eur|e|di|da|con|in|per")) {
                if (t.matches("euro|eur") && currentBuffer.length() > 0) {
                    grandTotal = grandTotal.add(parseStringBuffer(currentBuffer.toString(), units, tens));
                    currentBuffer.setLength(0);
                }
                continue;
            }

            boolean isDigit = t.matches("\\d+(?:[.,]\\d+)*");
            boolean isKeyword = isValidNumberWord(t);
            boolean isUn = t.matches("un|una|uno");
            boolean nextIsCents = isFollowedByCents(tokens, i + 1);

            if (isDigit) {
                foundSomething = true;
                BigDecimal val = parseSmartDecimal(t);

                if (nextIsCents) {
                    val = val.multiply(new BigDecimal("0.01"));
                }

                boolean likelyDecimal = t.contains(",") || (t.contains(".") && !t.matches(".*\\.\\d{3}$"));

                if (val.compareTo(BigDecimal.valueOf(1000)) >= 0 || nextIsCents || likelyDecimal) {
                    if (currentBuffer.length() > 0) {
                        grandTotal = grandTotal.add(parseStringBuffer(currentBuffer.toString(), units, tens));
                        currentBuffer.setLength(0);
                    }
                    grandTotal = grandTotal.add(val);
                } else {
                    currentBuffer.append(t);
                }

            } else if (isKeyword) {
                foundSomething = true;
                if (isUn) {
                    if (isFollowedByMagnitude(tokens, i + 1)) {
                        currentBuffer.append(t);
                    }
                } else {
                    currentBuffer.append(t);
                }

                if (nextIsCents) {
                    BigDecimal val = parseStringBuffer(currentBuffer.toString(), units, tens);
                    val = val.multiply(new BigDecimal("0.01"));
                    grandTotal = grandTotal.add(val);
                    currentBuffer.setLength(0);
                }
            }
        }

        if (currentBuffer.length() > 0) {
            grandTotal = grandTotal.add(parseStringBuffer(currentBuffer.toString(), units, tens));
        }

        return foundSomething ? grandTotal : null;
    }

    /**
     * Parses a string representing a decimal number into a BigDecimal.
     * This method is able to handle strings with comma or dot as decimal separator.
     * If the string contains more than one dot, or if it contains a comma but no
     * dot, it will be treated as a string with comma as decimal separator.
     * If the string contains only one dot, it will be treated as a string with dot
     * as decimal separator.
     * If the string contains neither comma nor dot, it will be treated as a string
     * with no decimal separator.
     * If the string is null, this method will return BigDecimal.ZERO.
     * 
     * @param numStr the string to be parsed
     * @return a BigDecimal representing the parsed string
     */
    private BigDecimal parseSmartDecimal(String numStr) {
        if (numStr == null)
            return BigDecimal.ZERO;
        String cleanNum = numStr;
        try {
            boolean hasDot = cleanNum.contains(".");
            boolean hasComma = cleanNum.contains(",");

            if (hasDot && hasComma) {
                cleanNum = cleanNum.replace(".", "").replace(",", ".");
            } else if (hasComma) {
                cleanNum = cleanNum.replace(",", ".");
            } else if (hasDot) {
                int dotsCount = org.springframework.util.StringUtils.countOccurrencesOf(cleanNum, ".");

                if (cleanNum.matches(".*\\.\\d{2}$")) {
                    return new BigDecimal(cleanNum);
                }

                if (dotsCount > 1 || cleanNum.matches(".*\\.\\d{3}$")) {
                    cleanNum = cleanNum.replace(".", "");
                }
            }
            return new BigDecimal(cleanNum);
        } catch (Exception e) {
            return BigDecimal.ZERO;
        }
    }

    /**
     * Parses a string representing a decimal number into a BigDecimal.
     * This method is able to handle strings with comma or dot as decimal separator.
     * If the string contains more than one dot, or if it contains a comma but no
     * dot, it will be treated as a string with comma as decimal separator.
     * If the string contains only one dot, it will be treated as a string with dot
     * as decimal separator.
     * If the string contains neither comma nor dot, it will be treated as a string
     * with no decimal separator.
     * If the string is null, this method will return BigDecimal.ZERO.
     * 
     * @param raw   the string to be parsed
     * @param units a map containing the correspondence between number words and
     *              their values
     * @param tens  a map containing the correspondence between tens words and their
     *              values
     * @return a BigDecimal representing the parsed string
     */
    private BigDecimal parseStringBuffer(String raw, Map<String, Long> units, Map<String, Long> tens) {
        String normalized = raw.replace(".", "").replace(",", "");
        normalized = normalized.replaceAll("milae", "mila");
        normalized = normalized.replaceAll("milionie", "milioni");

        try {
            long res = parseGroup(normalized, units, tens);
            return BigDecimal.valueOf(res);
        } catch (Exception e) {
            return BigDecimal.ZERO;
        }
    }

    /**
     * Returns true if the given string is a valid number word, false otherwise.
     * A valid number word is a string that matches the following regular
     * expression:
     * ".*(uno|due|tre|quattro|cinque|sei|sette|otto|nove|dieci|undici|dodici|tredici|quattordici|quindici|sedici|diciassette|diciotto|diciannove|venti|trenta|quaranta|cinquanta|sessanta|settanta|ottanta|novanta|cento|mille|mila|milion|miliard).*"
     * 
     * @param s the string to be checked
     * @return true if the string is a valid number word, false otherwise
     */
    private boolean isValidNumberWord(String s) {
        return s.matches(
                ".*(uno|due|tre|quattro|cinque|sei|sette|otto|nove|dieci|undici|dodici|tredici|quattordici|quindici|sedici|diciassette|diciotto|diciannove|venti|trenta|quaranta|cinquanta|sessanta|settanta|ottanta|novanta|cento|mille|mila|milion|miliard).*");
    }

    private boolean isFollowedByMagnitude(String[] tokens, int startIndex) {
        for (int i = startIndex; i < tokens.length; i++) {
            String t = tokens[i];
            if (t.matches("euro|eur|e|di|da|con|in|per"))
                continue;
            if (t.matches(".*(milion|miliard|mila|mille|cento).*"))
                return true;
            return false;
        }
        return false;
    }

    /**
     * Returns true if the given string array contains a cent word (or its plural
     * form) starting from the given index.
     * A cent word is a string that matches the following regular expression:
     * ".*(centesi|cent).*"
     * 
     * @param tokens     the string array to be checked
     * @param startIndex the index from which to start the check
     * @return true if the string array contains a cent word starting from the given
     *         index, false otherwise
     */
    private boolean isFollowedByCents(String[] tokens, int startIndex) {
        for (int i = startIndex; i < tokens.length; i++) {
            String t = tokens[i];
            if (t.matches("e|di|da|con"))
                continue;
            if (t.startsWith("centesi") || t.equals("cent"))
                return true;
            return false;
        }
        return false;
    }

    /**
     * Parses a group of words representing a number in Italian.
     * A group is a sequence of words that together represent a number, e.g. "due
     * milioni".
     * 
     * @param s     the string to be parsed
     * @param units a map of unit words to their corresponding values
     * @param tens  a map of tens words to their corresponding values
     * @return the parsed number
     */
    private long parseGroup(String s, Map<String, Long> units, Map<String, Long> tens) {
        long total = 0;
        if (s.contains("miliard")) {
            int idx = s.indexOf("miliard");
            String before = s.substring(0, idx);
            int end = idx + "miliard".length();
            if (end < s.length() && (s.charAt(end) == 'o' || s.charAt(end) == 'i'))
                end++;
            long multiplier = before.isEmpty() ? 1 : parseSmallNumber(before, units, tens);
            total += multiplier * 1_000_000_000L;
            s = s.substring(end);
        }
        if (s.contains("milion")) {
            int idx = s.indexOf("milion");
            String before = s.substring(0, idx);
            int end = idx + "milion".length();
            if (end < s.length() && (s.charAt(end) == 'e' || s.charAt(end) == 'i'))
                end++;
            long multiplier = before.isEmpty() ? 1 : parseSmallNumber(before, units, tens);
            total += multiplier * 1_000_000L;
            s = s.substring(end);
        }
        if (s.contains("mila")) {
            int idx = s.indexOf("mila");
            String before = s.substring(0, idx);
            long multiplier = before.isEmpty() ? 1 : parseSmallNumber(before, units, tens);
            total += multiplier * 1_000L;
            s = s.substring(idx + "mila".length());
        } else if (s.contains("mille")) {
            int idx = s.indexOf("mille");
            String before = s.substring(0, idx);
            long multiplier = before.isEmpty() ? 1 : parseSmallNumber(before, units, tens);
            total += multiplier * 1_000L;
            s = s.substring(idx + "mille".length());
        }
        if (!s.isEmpty()) {
            total += parseSmallNumber(s, units, tens);
        }
        return total;
    }

    /**
     * Parses a string representing a small number in Italian into a long.
     * A small number is a number less than 1000, e.g. "duecento quaranta" or
     * "millecinquecento".
     * This method is able to handle strings with comma or dot as decimal separator.
     * If the string contains more than one dot, or if it contains a comma but no
     * dot, it will be treated as a string with comma as decimal separator.
     * If the string contains only one dot, it will be treated as a string with dot
     * as decimal separator.
     * If the string contains neither comma nor dot, it will be treated as a string
     * with no decimal separator.
     * If the string is null, this method will return 0.
     * 
     * @param s     the string to be parsed
     * @param units a map containing the correspondence between number words and
     *              their values
     * @param tens  a map containing the correspondence between tens words and their
     *              values
     * @return the parsed number
     */
    private long parseSmallNumber(String s, Map<String, Long> units, Map<String, Long> tens) {
        if (s == null || s.isEmpty())
            return 0;
        if (s.matches("\\d+"))
            return Long.parseLong(s);

        long result = 0;
        String[] centinaia = { "novecento", "ottocento", "settecento", "seicento", "cinquecento", "quattrocento",
                "trecento", "duecento", "cento", "cent" };
        long[] centValues = { 900, 800, 700, 600, 500, 400, 300, 200, 100, 100 };

        for (int i = 0; i < centinaia.length; i++) {
            if (s.startsWith(centinaia[i])) {
                result += centValues[i];
                s = s.substring(centinaia[i].length());
                break;
            }
        }
        if (s.isEmpty())
            return result;

        String[] orderedTens = {
                "diciassette", "diciotto", "diciannove",
                "quattordici", "quindici", "sedici", "tredici", "dodici", "undici", "dieci",
                "novanta", "novant", "ottanta", "ottant", "settanta", "settant",
                "sessanta", "sessant", "cinquanta", "cinquant", "quaranta", "quarant",
                "trenta", "trent", "venti", "vent"
        };
        for (String t : orderedTens) {
            if (s.startsWith(t)) {
                long val = tens.getOrDefault(t, units.getOrDefault(t, 0L));
                result += val;
                s = s.substring(t.length());
                break;
            }
        }
        if (s.isEmpty())
            return result;

        String[] orderedUnits = { "quattro", "cinque", "sette", "otto", "nove", "uno", "una", "un", "due", "tre",
                "sei" };
        for (String u : orderedUnits) {
            if (s.startsWith(u)) {
                result += units.getOrDefault(u, 0L);
                break;
            }
        }
        return result;
    }

    /**
     * Replaces all occurrences of verb words in the given text with their
     * corresponding noun words.
     * The replacements are done according to the VERB_TO_NOUN map.
     * 
     * @param text the string to be processed
     * @return the processed string with all verb words replaced with their noun
     *         words
     */
    private String convertVerbsToNouns(String text) {
        String converted = text;
        for (Map.Entry<String, String> entry : VERB_TO_NOUN.entrySet()) {
            converted = converted.replaceAll("(?i)\\b" + entry.getKey() + "\\b", entry.getValue());
        }
        return converted;
    }

    /**
     * Extracts the ID of a transaction from the given text.
     * The ID is assumed to be the first occurrence of a number
     * following the words "id", "numero", "codice" or "transazione".
     * If no ID is found, the method returns null.
     * 
     * @param text the string from which to extract the ID
     * @return the extracted ID, or null if no ID is found
     */
    private Long extractId(String text) {
        Matcher m = ID_PATTERN.matcher(text);
        if (m.find())
            return Long.parseLong(m.group(1));
        return null;
    }

    /**
     * Extracts a smart description from the given text.
     * The description is extracted by following these rules:
     * 1. If the text contains a specific noun (e.g. "netflix", "spotify"), it is
     * returned capitalized.
     * 2. If the text does not contain a specific noun, the description is formed by
     * taking the first two words of the text that have a length greater than 2.
     * 3. If the description is empty or has a length less than 3, a default
     * description is returned based on the category.
     * 4. If the description has a length greater than 30, it is truncated to 27
     * characters and "..." is appended.
     * 
     * @param text           the string from which to extract the description
     * @param category       the category of the transaction (e.g. "Pagamento",
     *                       "Acquisto")
     * @param matchedKeyword the keyword that was matched in the text (e.g.
     *                       "pagamento", "acquisto")
     * @return the extracted description
     */
    private String extractSmartDescription(String text, String category, String matchedKeyword) {
        String specificNoun = extractSpecificNoun(text);
        if (specificNoun != null && !specificNoun.isEmpty()) {
            return StringUtils.capitalize(specificNoun);
        }
        String cleaned = cleanText(text);
        String[] words = cleaned.split("\\s+");
        StringBuilder result = new StringBuilder();
        int count = 0;
        for (String word : words) {
            if (word.length() > 2 && count < 2) {
                if (result.length() > 0)
                    result.append(" ");
                result.append(word);
                count++;
            }
        }
        String finalDesc = result.toString().trim();
        if (finalDesc.isEmpty() || finalDesc.length() < 3) {
            if (category != null && CATEGORY_DEFAULT_DESC.containsKey(category)) {
                return CATEGORY_DEFAULT_DESC.get(category);
            }
            return category != null ? category : "Generale";
        }
        finalDesc = StringUtils.capitalize(finalDesc);
        if (finalDesc.length() > 30)
            finalDesc = finalDesc.substring(0, 27) + "...";
        return finalDesc;
    }

    /**
     * Extracts a specific noun from the given text.
     * The noun is searched from a predefined list of specific nouns.
     * If the noun is found, it is returned in lowercase.
     * If no noun is found, the method returns null.
     * 
     * @param text the string from which to extract the noun
     * @return the extracted noun, or null if no noun is found
     */
    private String extractSpecificNoun(String text) {
        String lowerText = text.toLowerCase();
        String[] specificNouns = {
                "netflix", "spotify", "amazon", "prime", "disney",
                "pizza", "sushi", "hamburger", "kebab",
                "mcdonald", "burger king", "kfc",
                "esselunga", "coop", "lidl", "conad", "carrefour", "eurospin",
                "benzina", "diesel", "carburante", "metano", "gpl",
                "treno", "bus", "metro", "taxi", "uber", "aereo",
                "farmacia", "parafarmacia", "ospedale",
                "palestra", "piscina", "padel", "tennis", "calcio",
                "parrucchiere", "barbiere", "estetista", "manicure", "pedicure",
                "massaggio", "spa", "trucco", "makeup", "profumeria",
                "enel", "tim", "vodafone", "wind", "fastweb",
                "affitto", "mutuo", "condominio",
                "zalando", "zara", "h&m", "shein", "vinted",
                "ikea", "leroy", "obi",
                "abbonamento", "biglietto", "manutenzione", "slot"
        };
        for (String noun : specificNouns) {
            if (lowerText.contains(noun))
                return noun;
        }
        return null;
    }

    /**
     * Clean the given text from noise words and phrases.
     * The method performs the following operations:
     * Replace all occurrences of numbers with a space
     * replace all occurrences of "euro" and "eur" with a space
     * replace all occurrences of "id", "numero", "codice", "transazione" followed
     * by a space and a number with a space
     * replace all occurrences of "modifica", "cambia", "aggiorna", "elimina",
     * "cancella", "rimuovi", "raggiungi" with a space
     * replace all occurrences of noise words and phrases with a space
     * replace all occurrences of multiple spaces with a single space
     * trim the resulting string
     * 
     * @param text the string to clean
     * @return the cleaned string
     */
    private String cleanText(String text) {
        String cleaned = text.toLowerCase();
        cleaned = cleaned.replaceAll("(\\d+(?:[.,]\\d+)*)", " ");
        cleaned = cleaned.replaceAll("[€$]|euro|eur", " ");
        cleaned = cleaned.replaceAll("(?:id|numero|codice|transazione)\\s+\\d+", " ");
        cleaned = cleaned.replaceAll("(modifica|cambia|aggiorna|elimina|cancella|rimuovi|raggiungi)", " ");

        String regex = "\\b(" +
                "johnny|jonny|gionni|gianni|jarfin|jar|" +
                "aggiungi|inserisci|crea|nuova|nuovo|registra|segna|metti|tieni|traccia|" +
                "ho|hai|ha|abbiamo|avete|hanno|" +
                "sono|sei|è|siamo|siete|" +
                "speso|pagato|pagamento|comprato|acquisto|preso|ricevuto|accreditato|uscito|entrato|ristorante|" +
                "fatto|faccio|fai|fa|" +
                "il|lo|la|i|gli|le|un|una|uno|" +
                "per|di|a|in|con|su|da|fra|tra|come|tipo|" +
                "del|dello|della|dei|degli|delle|" +
                "al|allo|alla|ai|agli|alle|" +
                "mi|ti|ci|vi|si|" +
                "e|o|ma|quindi|allora|anche|ancora|" +
                "non|no|sì|si|" +
                "zero|due|tre|quattro|cinque|sei|sette|otto|nove|dieci|" +
                "soldi|denaro|importo|prezzo|valore|costo|totale|cifra|centesimi|cent" +
                ")\\b";

        cleaned = cleaned.replaceAll(regex, " ");
        cleaned = cleaned.replaceAll(
                "\\b\\w*(milion|miliard|mila|mille|cento|cent|trent|quarant|cinquant|sessant|settant|ottant|novant|venti|undici|dodici)\\w*\\b",
                " ");

        return cleaned.trim().replaceAll("\\s+", " ");
    }

    /**
     * Checks if the given text contains any of the given keywords.
     * This method will return true as soon as it finds a keyword in the text, and
     * false otherwise.
     * 
     * @param text     the text to search for keywords
     * @param keywords the keywords to look for
     * @return true if any of the keywords are found, false otherwise
     */
    private boolean containsAny(String text, String... keywords) {
        for (String k : keywords)
            if (text.contains(k))
                return true;
        return false;
    }
}