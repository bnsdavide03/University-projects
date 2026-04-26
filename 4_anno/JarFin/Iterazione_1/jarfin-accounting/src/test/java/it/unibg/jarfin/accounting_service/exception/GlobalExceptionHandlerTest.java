package it.unibg.jarfin.accounting_service.exception;

import static org.junit.jupiter.api.Assertions.assertEquals;
import static org.junit.jupiter.api.Assertions.assertTrue;
import static org.mockito.Mockito.mock;
import static org.mockito.Mockito.when;

import java.util.Collections;
import java.util.List;
import java.util.Map;

import jakarta.persistence.EntityNotFoundException;
import org.junit.jupiter.api.Test;
import org.springframework.http.HttpStatus;
import org.springframework.http.ResponseEntity;
import org.springframework.validation.BindingResult;
import org.springframework.validation.FieldError;
import org.springframework.web.bind.MethodArgumentNotValidException;

class GlobalExceptionHandlerTest {

    private final GlobalExceptionHandler handler = new GlobalExceptionHandler();

    /**
     * Test case for handleNotFound method.
     * Verify that the response contains the correct HTTP status (404) and error message.
     */
    @Test
    void testHandleNotFound() {
        String errorMessage = "Transazione ID 1 non trovata";
        EntityNotFoundException exception = new EntityNotFoundException(errorMessage);

        ResponseEntity<String> response = handler.handleNotFound(exception);

        assertEquals(HttpStatus.NOT_FOUND, response.getStatusCode());
        assertEquals(errorMessage, response.getBody());
    }

    /**
     * Test case for handleValidationErrors method.
     * Verify that the response contains the correct HTTP status (400) and a JSON body containing the error message.
     */
    @Test
    void testHandleValidationErrors() {
    	BindingResult bindingResult = mock(BindingResult.class);
        
        FieldError fieldError = new FieldError("transactionRequest", "amount", "must be positive");
        
        when(bindingResult.getFieldErrors()).thenReturn(List.of(fieldError));

        MethodArgumentNotValidException exception = new MethodArgumentNotValidException(null, bindingResult);

        ResponseEntity<Map<String, String>> response = handler.handleValidationErrors(exception);

        assertEquals(HttpStatus.BAD_REQUEST, response.getStatusCode());
        
        Map<String, String> body = response.getBody();
        assertTrue(body.containsKey("amount"));
        assertEquals("must be positive", body.get("amount"));
    }
    
    /**
     * Test case for handleValidationErrors method when the BindingResult is empty.
     * Verify that the response contains the correct HTTP status (400) and an empty JSON body.
     */
    @Test
    void testHandleValidationErrors_Empty() {
        BindingResult bindingResult = mock(BindingResult.class);
        when(bindingResult.getFieldErrors()).thenReturn(Collections.emptyList());
        
        MethodArgumentNotValidException exception = new MethodArgumentNotValidException(null, bindingResult);

        ResponseEntity<Map<String, String>> response = handler.handleValidationErrors(exception);

        assertEquals(HttpStatus.BAD_REQUEST, response.getStatusCode());
        assertTrue(response.getBody().isEmpty());
    }
}