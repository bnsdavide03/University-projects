package it.unibg.jarfin.web.controller;

import static org.mockito.ArgumentMatchers.any;
import static org.mockito.ArgumentMatchers.anyString;
import static org.mockito.ArgumentMatchers.eq;
import static org.mockito.Mockito.verify;
import static org.mockito.Mockito.when;
import static org.springframework.test.web.servlet.request.MockMvcRequestBuilders.post;
import static org.springframework.test.web.servlet.result.MockMvcResultMatchers.jsonPath;
import static org.springframework.test.web.servlet.result.MockMvcResultMatchers.status;

import java.math.BigDecimal;
import java.time.LocalDate;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

import org.junit.jupiter.api.BeforeEach;
import org.junit.jupiter.api.DisplayName;
import org.junit.jupiter.api.Test;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.boot.test.autoconfigure.web.servlet.WebMvcTest;
import org.springframework.boot.test.mock.mockito.MockBean;
import org.springframework.core.ParameterizedTypeReference;
import org.springframework.http.HttpEntity;
import org.springframework.http.HttpMethod;
import org.springframework.http.HttpStatus;
import org.springframework.http.MediaType;
import org.springframework.http.ResponseEntity;
import org.springframework.test.web.servlet.MockMvc;
import org.springframework.web.client.RestTemplate;

import com.fasterxml.jackson.databind.ObjectMapper;

import it.unibg.jarfin.web.dto.CommandType;
import it.unibg.jarfin.web.dto.ParsedTransaction;
import it.unibg.jarfin.web.service.NaturalLanguageService;

@WebMvcTest(CommandController.class)
class CommandControllerTest {

        @Autowired
        private MockMvc mockMvc;

        @MockBean
        private NaturalLanguageService nluService;

        @MockBean
        private RestTemplate restTemplate;

        @Autowired
        private ObjectMapper objectMapper;

        private Map<String, String> payload;
        private ParsedTransaction parsedTx;

        /**
         * Initializes the payload and parsedTx objects before each test with sample
         * data.
         * 
         * @implNote The payload object is a HashMap containing the text to be
         *           processed.
         *           The parsedTx object is a ParsedTransaction containing the
         *           description, amount and date of the transaction.
         */
        @BeforeEach
        void setUp() {
                payload = new HashMap<>();
                payload.put("text", "Comando di prova");

                parsedTx = new ParsedTransaction();
                parsedTx.setDescription("Test");
                parsedTx.setAmount(new BigDecimal("10.00"));
                parsedTx.setDate(LocalDate.now());
        }

        /**
         * Verifies that a CREATE request is successfully processed by invoking a POST
         * to the Gateway.
         * The test first mocks the NLU service to return a parsed CREATE request and
         * then
         * verifies that the Gateway is invoked with the correct parameters.
         * Finally, the test verifies that the response contains the correct message.
         */
        @Test
        @DisplayName("CREATE: Dovrebbe inviare una POST al Gateway")
        void process_Create_Success() throws Exception {
                parsedTx.setCommandType(CommandType.CREATE);
                when(nluService.parse(anyString())).thenReturn(parsedTx);

                when(restTemplate.postForEntity(anyString(), any(HttpEntity.class), eq(Object.class)))
                                .thenReturn(new ResponseEntity<>(HttpStatus.OK));

                mockMvc.perform(post("/api/voice/process")
                                .contentType(MediaType.APPLICATION_JSON)
                                .content(objectMapper.writeValueAsString(payload)))
                                .andExpect(status().isOk())
                                .andExpect(jsonPath("$.message").value("Salvato"));
        }

        /**
         * Verifies that an UPDATE request is successfully processed by first invoking a
         * GET
         * to check if the transaction exists and then invoking a PUT to update the
         * transaction.
         * The test first mocks the NLU service to return a parsed UPDATE request and
         * then mocks
         * the Gateway to return the existing transaction.
         * Finally, the test verifies that the response contains the correct message and
         * that the
         * Gateway is invoked with the correct parameters.
         */
        @Test
        @DisplayName("UPDATE: Dovrebbe fare GET (check) e poi PUT")
        void process_Update_Success() throws Exception {
                parsedTx.setCommandType(CommandType.UPDATE);
                parsedTx.setTargetId(1L);
                when(nluService.parse(anyString())).thenReturn(parsedTx);

                ParsedTransaction existing = new ParsedTransaction();
                existing.setId(1L);
                existing.setAmount(new BigDecimal("5.00"));

                when(restTemplate.getForObject(anyString(), eq(ParsedTransaction.class)))
                                .thenReturn(existing);

                mockMvc.perform(post("/api/voice/process")
                                .contentType(MediaType.APPLICATION_JSON)
                                .content(objectMapper.writeValueAsString(payload)))
                                .andExpect(status().isOk())
                                .andExpect(jsonPath("$.message").value("Aggiornato ID 1"));

                verify(restTemplate).put(anyString(), any(HttpEntity.class));
        }

        /**
         * Verifies that an UPDATE request fails with a 400 Bad Request if the
         * transaction ID does not exist.
         * The test first mocks the NLU service to return a parsed UPDATE request and
         * then mocks
         * the Gateway to return null.
         * Finally, the test verifies that the response contains the correct message and
         * that the
         * Gateway is invoked with the correct parameters.
         */
        @Test
        @DisplayName("UPDATE: Fallisce se ID non esiste")
        void process_Update_NotFound() throws Exception {
                parsedTx.setCommandType(CommandType.UPDATE);
                parsedTx.setTargetId(99L);
                when(nluService.parse(anyString())).thenReturn(parsedTx);

                when(restTemplate.getForObject(anyString(), eq(ParsedTransaction.class)))
                                .thenReturn(null);

                mockMvc.perform(post("/api/voice/process")
                                .contentType(MediaType.APPLICATION_JSON)
                                .content(objectMapper.writeValueAsString(payload)))
                                .andExpect(status().isBadRequest())
                                .andExpect(jsonPath("$").value("ID non trovato."));
        }

        /**
         * Verifies that a DELETE request is successfully processed by invoking a POST
         * to the Gateway.
         * The test first mocks the NLU service to return a parsed DELETE request and
         * then
         * verifies that the Gateway is invoked with the correct parameters.
         * Finally, the test verifies that the response contains the correct message.
         */
        @Test
        @DisplayName("DELETE: Elimina per ID diretto")
        void process_Delete_ById_Success() throws Exception {
                parsedTx.setCommandType(CommandType.DELETE);
                parsedTx.setTargetId(5L);
                when(nluService.parse(anyString())).thenReturn(parsedTx);

                mockMvc.perform(post("/api/voice/process")
                                .contentType(MediaType.APPLICATION_JSON)
                                .content(objectMapper.writeValueAsString(payload)))
                                .andExpect(status().isOk())
                                .andExpect(jsonPath("$.message").value("Eliminato ID 5"));

                verify(restTemplate).delete(anyString());
        }

        /**
         * Verifies that a DELETE request is successfully processed by invoking a POST
         * to the Gateway,
         * which in turn invokes a GET to the Accounting Service to retrieve the list of
         * transactions with the
         * specified description. The test then verifies that the Gateway is invoked
         * with the correct
         * parameters and that the response contains the correct message.
         */
        @Test
        @DisplayName("DELETE: Elimina cercando per Descrizione (Logica complessa)")
        void process_Delete_ByDescription_Success() throws Exception {
                parsedTx.setCommandType(CommandType.DELETE);
                parsedTx.setTargetId(null);
                parsedTx.setDescription("pizza");
                when(nluService.parse(anyString())).thenReturn(parsedTx);

                ParsedTransaction t1 = new ParsedTransaction();
                t1.setId(10L);
                t1.setDescription("Pizza margherita");
                t1.setDate(LocalDate.now().minusDays(1));

                ParsedTransaction t2 = new ParsedTransaction();
                t2.setId(20L);
                t2.setDescription("Pizza diavola");
                t2.setDate(LocalDate.now());

                List<ParsedTransaction> mockList = new ArrayList<>();
                mockList.add(t1);
                mockList.add(t2);

                when(restTemplate.exchange(
                                anyString(),
                                eq(HttpMethod.GET),
                                eq(null),
                                any(ParameterizedTypeReference.class)))
                                .thenReturn(ResponseEntity.ok(mockList));

                mockMvc.perform(post("/api/voice/process")
                                .contentType(MediaType.APPLICATION_JSON)
                                .content(objectMapper.writeValueAsString(payload)))
                                .andExpect(status().isOk())
                                .andExpect(jsonPath("$.message").value("Eliminato ID 20"));

                verify(restTemplate).delete(anyString());
        }

        /**
         * Verifies that if the parser returns null or an incomplete command, the
         * request is ignored and
         * the response contains the status "ignored".
         */
        @Test
        @DisplayName("IGNORED: Se il parser restituisce null o comando incompleto")
        void process_Ignored() throws Exception {
                when(nluService.parse(anyString())).thenReturn(null);

                mockMvc.perform(post("/api/voice/process")
                                .contentType(MediaType.APPLICATION_JSON)
                                .content(objectMapper.writeValueAsString(payload)))
                                .andExpect(status().isOk())
                                .andExpect(jsonPath("$.status").value("ignored"));
        }
}