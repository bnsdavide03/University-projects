package it.unibg.jarfin.web.controller;

import it.unibg.jarfin.web.dto.ParsedTransaction;
import it.unibg.jarfin.web.dto.CommandType;
import it.unibg.jarfin.web.service.NaturalLanguageService;
import lombok.extern.slf4j.Slf4j;
import org.springframework.beans.factory.annotation.Value;
import org.springframework.core.ParameterizedTypeReference;
import org.springframework.http.HttpEntity;
import org.springframework.http.HttpHeaders;
import org.springframework.http.HttpMethod;
import org.springframework.http.MediaType;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;
import org.springframework.web.client.RestTemplate;

import java.math.BigDecimal;
import java.util.Comparator;
import java.util.List;
import java.util.Map;
import java.util.Optional;

@RestController
@RequestMapping("/api/voice")
@Slf4j
public class CommandController {

    private final NaturalLanguageService nluService;
    private final RestTemplate restTemplate;

    private static final String RESPONSE_KEY_MESSAGE = "message";

    @Value("${api.gateway.url:http://localhost:8080}")
    private String gatewayUrl;

    public CommandController(NaturalLanguageService nluService, RestTemplate restTemplate) {
        this.nluService = nluService;
        this.restTemplate = restTemplate;
    }

    /**
     * Processes a voice command received from the frontend.
     *
     * @param payload A Map containing the text of the voice command.
     * @return A ResponseEntity containing the response message.
     */
    @PostMapping("/process")
    public ResponseEntity<?> processVoiceCommand(@RequestBody Map<String, String> payload) {
        String text = payload.get("text");
        log.info("Comando vocale: {}", text);

        try {
            ParsedTransaction parsed = nluService.parse(text);

            if (parsed == null || (parsed.getAmount() == null && parsed.getCommandType() == CommandType.CREATE)) {
                return ResponseEntity.ok(Map.of("status", "ignored", RESPONSE_KEY_MESSAGE, "Ignorato"));
            }

            String baseUrl = gatewayUrl + "/api/transactions";
            HttpHeaders headers = new HttpHeaders();
            headers.setContentType(MediaType.APPLICATION_JSON);

            switch (parsed.getCommandType()) {
                case CREATE:
                    if (parsed.getType() == null)
                        parsed.setType("EXPENSE");
                    if (parsed.getCategory() == null)
                        parsed.setCategory("Altro");
                    if (parsed.getDescription() == null || parsed.getDescription().isEmpty())
                        parsed.setDescription("Transazione Vocale");
                    if (parsed.getAmount() == null)
                        parsed.setAmount(BigDecimal.ZERO);

                    HttpEntity<ParsedTransaction> request = new HttpEntity<>(parsed, headers);
                    restTemplate.postForEntity(baseUrl, request, Object.class);

                    return ResponseEntity.ok(Map.of(
                            RESPONSE_KEY_MESSAGE, "Salvato",
                            "amount", parsed.getAmount(),
                            "category", parsed.getCategory()));

                case UPDATE:
                    if (parsed.getTargetId() == null)
                        return ResponseEntity.badRequest().body("Specifica l'ID");

                    String resourceUrl = baseUrl + "/" + parsed.getTargetId();
                    ParsedTransaction existing = null;

                    try {
                        existing = restTemplate.getForObject(resourceUrl, ParsedTransaction.class);
                    } catch (Exception e) {
                        return ResponseEntity.badRequest().body("ID non trovato o errore backend.");
                    }

                    if (existing == null)
                        return ResponseEntity.badRequest().body("ID non trovato.");

                    if (parsed.getAmount() != null)
                        existing.setAmount(parsed.getAmount());
                    if (parsed.getDescription() != null && !parsed.getDescription().isEmpty())
                        existing.setDescription(parsed.getDescription());
                    if (parsed.getCategory() != null)
                        existing.setCategory(parsed.getCategory());
                    if (parsed.getType() != null)
                        existing.setType(parsed.getType());

                    existing.setId(parsed.getTargetId());

                    HttpEntity<ParsedTransaction> requestUp = new HttpEntity<>(existing, headers);
                    restTemplate.put(resourceUrl, requestUp);

                    return ResponseEntity.ok(Map.of(
                            RESPONSE_KEY_MESSAGE, "Aggiornato ID " + parsed.getTargetId(),
                            "amount", existing.getAmount(),
                            "category", "Modifica salvata"));

                case DELETE:
                    Long idToDelete = parsed.getTargetId();

                    if (idToDelete == null && parsed.getDescription() != null) {
                        idToDelete = findIdByDescription(parsed.getDescription());
                    }

                    if (idToDelete == null)
                        return ResponseEntity.badRequest().body("Non ho capito cosa eliminare (ID mancante).");

                    try {
                        restTemplate.delete(baseUrl + "/" + idToDelete);
                        return ResponseEntity.ok(Map.of(RESPONSE_KEY_MESSAGE, "Eliminato ID " + idToDelete));
                    } catch (Exception e) {
                        return ResponseEntity.badRequest().body("Errore durante l'eliminazione dell'ID " + idToDelete);
                    }

                default:
                    return ResponseEntity.status(400).body("Comando sconosciuto");
            }

        } catch (Exception e) {
            log.error("Errore processamento voce: {}", e.getMessage());
            return ResponseEntity.internalServerError().body("Errore: " + e.getMessage());
        }
    }

    /**
     * Find the ID of a transaction by keyword in description.
     *
     * @param keyword the keyword to search in the transaction description
     * @return the ID of the transaction with the given keyword, or null if not
     *         found
     */
    private Long findIdByDescription(String keyword) {
        try {
            ResponseEntity<List<ParsedTransaction>> response = restTemplate.exchange(
                    gatewayUrl + "/api/transactions",
                    HttpMethod.GET,
                    null,
                    new ParameterizedTypeReference<List<ParsedTransaction>>() {
                    });

            List<ParsedTransaction> list = response.getBody();
            if (list == null || list.isEmpty())
                return null;

            Optional<ParsedTransaction> match = list.stream()
                    .filter(t -> t.getDescription() != null
                            && t.getDescription().toLowerCase().contains(keyword.toLowerCase()))
                    .sorted(Comparator.comparing(ParsedTransaction::getDate,
                            Comparator.nullsLast(Comparator.reverseOrder())))
                    .findFirst();

            return match.map(ParsedTransaction::getId).orElse(null);

        } catch (Exception e) {
            log.warn("Errore durante findIdByDescription: {}", e.getMessage());
            return null;
        }
    }
}