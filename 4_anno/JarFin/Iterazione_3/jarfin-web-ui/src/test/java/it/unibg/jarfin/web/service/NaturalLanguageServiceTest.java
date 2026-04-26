package it.unibg.jarfin.web.service;

import it.unibg.jarfin.web.dto.CommandType;
import it.unibg.jarfin.web.dto.ParsedTransaction;
import org.junit.jupiter.api.Test;
import java.math.BigDecimal;

import static org.junit.jupiter.api.Assertions.*;

class NaturalLanguageServiceTest {

    private final NaturalLanguageService service = new NaturalLanguageService();

    /**
     * Tests that the NLU service recognizes amounts expressed in millions of euros.
     * The input text "spesa di 186 milioni" must be parsed into a ParsedTransaction
     * object with an amount of 186,000,000.
     */
    @Test
    void testMillionsAndThousands() {
        ParsedTransaction t = service.parse("spesa di 186 milioni");
        assertAmount("186000000", t);
    }

    /**
     * Tests that the NLU service is able to recognize an amount expressed as a
     * complex compound number, such as "duecentomila e cinquecento euro".
     * The input text must be converted into a ParsedTransaction object with an
     * amount of 200500.
     */
    @Test
    void testComplexCompoundNumber() {
        ParsedTransaction t = service.parse("ho pagato duecentomila e cinquecento euro");
        assertAmount("200500", t);
    }

    /**
     * Tests that the NLU service recognizes amounts expressed as a mix of digits
     * and
     * words. The input text "ho speso 10 mila euro" must be parsed into a
     * ParsedTransaction object with an amount of 10,000.
     */
    @Test
    void testMixedDigitsAndWords() {
        ParsedTransaction t = service.parse("ho speso 10 mila euro");
        assertAmount("10000", t);
    }

    /**
     * Tests that the NLU service recognizes amounts expressed in words, such as
     * "venti euro e cinquanta centesimi". The input text must be parsed into a
     * ParsedTransaction object with an amount of 20.50.
     */
    @Test
    void testCentsInWords() {
        ParsedTransaction t = service.parse("venti euro e cinquanta centesimi");
        assertAmount("20.50", t);
    }

    /**
     * Tests that the NLU service recognizes amounts expressed as a mix of digits
     * and words, and that it can parse amounts containing both euros and cents.
     * The input text "pagato 47 e 83 centesimi" must be parsed into a
     * ParsedTransaction
     * object with an amount of 47.83.
     */
    @Test
    void testMixedDigitsAndCents() {
        ParsedTransaction t = service.parse("pagato 47 e 83 centesimi");
        assertAmount("47.83", t);
    }

    /**
     * Tests that the NLU service recognizes amounts expressed only in cents.
     * The input text "solo ottanta centesimi" must be parsed into a
     * ParsedTransaction
     * object with an amount of 0.80.
     */
    @Test
    void testOnlyCents() {
        ParsedTransaction t = service.parse("solo ottanta centesimi");
        assertAmount("0.80", t);
    }

    /**
     * Tests that the NLU service is able to recognize and categorize specific
     * keywords from the description of a transaction. In this case, the
     * description "pranzo al mcdonald" must be parsed into a ParsedTransaction
     * object with a category of "Ristorante" and a description of "Mcdonald".
     */
    @Test
    void testSmartDescriptionSpecific() {
        ParsedTransaction t = service.parse("pranzo al mcdonald");
        assertEquals("Ristorante", t.getCategory());
        assertEquals("Mcdonald", t.getDescription());
    }

    /**
     * Tests that the NLU service is able to recognize and categorize generic
     * keywords from the description of a transaction. In this case, the
     * description "ho pagato al ristorante" must be parsed into a ParsedTransaction
     * object with a category of "Ristorante" and a description of "Pranzo/Cena".
     */
    @Test
    void testSmartDescriptionGeneric() {
        ParsedTransaction t = service.parse("ho pagato al ristorante");
        assertEquals("Ristorante", t.getCategory());
        assertEquals("Pranzo/Cena", t.getDescription());
    }

    /**
     * Tests that the NLU service is able to recognize and categorize utility bills
     * from the description of a transaction. In this case, the description
     * "pagata bolletta enel" must be parsed into a ParsedTransaction object with
     * a category of "Bollette" and a description of "Enel".
     */
    @Test
    void testUtilityBills() {
        ParsedTransaction t = service.parse("pagata bolletta enel");
        assertEquals("Bollette", t.getCategory());
        assertEquals("Enel", t.getDescription());
    }

    /**
     * Tests that the NLU service recognizes a delete command and correctly
     * extracts the transaction ID from the input text.
     */
    @Test
    void testDeleteCommand() {
        ParsedTransaction t = service.parse("elimina transazione 55");
        assertEquals(CommandType.DELETE, t.getCommandType());
        assertEquals(55L, t.getTargetId());
    }

    /**
     * Tests that the NLU service recognizes an update command and correctly
     * extracts the transaction ID from the input text.
     */
    @Test
    void testUpdateCommand() {
        ParsedTransaction t = service.parse("modifica id 102");
        assertEquals(CommandType.UPDATE, t.getCommandType());
        assertEquals(102L, t.getTargetId());
    }

    /**
     * Tests that the NLU service recognizes an income command and correctly
     * extracts the category (in this case, "Stipendio") from the input text.
     */
    @Test
    void testIncomeRecognition() {
        ParsedTransaction t = service.parse("ricevuto stipendio");
        assertEquals("INCOME", t.getType());
        assertEquals("Stipendio", t.getCategory());
    }

    /**
     * Tests that the NLU service correctly resolves the ambiguity in the article
     * "una"
     * when it is used in the context of a transaction command.
     * In this case, the service should interpret the command as "aggiungi una
     * spesa"
     * and extract the amount 50 from the text.
     */
    @Test
    void testArticleAmbiguity() {
        ParsedTransaction t = service.parse("aggiungi una spesa di 50 euro");
        assertAmount("50", t);
    }

    /**
     * Tests that the NLU service correctly handles null or empty input strings,
     * returning null in both cases. The service is also tested with a single
     * character
     * string ("a") which should also return null.
     */
    @Test
    void testNullOrEmpty() {
        assertNull(service.parse(null));
        assertNull(service.parse(""));
        assertNull(service.parse("a"));
    }

    /**
     * Tests that the NLU service correctly handles stop words, returning null
     * if the input string contains only stop words.
     */
    @Test
    void testStopWords() {
        assertNull(service.parse("stop"));
        assertNull(service.parse("jarfin"));
        assertNull(service.parse("niente"));
    }

    /**
     * Tests that the NLU service correctly handles numeric formats, including
     * comma and period as decimal separators, and also handles thousands
     * separators.
     */
    @Test
    void testNumericFormats() {
        assertAmount("10.50", service.parse("speso 10.50"));
        assertAmount("10.50", service.parse("speso 10,50"));
        assertAmount("1000", service.parse("speso 1.000"));
    }

    /**
     * Asserts that the parsed transaction has an amount equal to the expected
     * value. If the transaction or its amount is null, or if the amount is not
     * equal to the expected value, an AssertionError is thrown.
     *
     * @param expected the expected amount value
     * @param t        the parsed transaction
     */
    private void assertAmount(String expected, ParsedTransaction t) {
        assertNotNull(t, "La transazione non dovrebbe essere null");
        assertNotNull(t.getAmount(), "L'importo non dovrebbe essere null");
        assertEquals(0, new BigDecimal(expected).compareTo(t.getAmount()),
                "Importo errato: atteso " + expected + ", ottenuto " + t.getAmount());
    }
}