package it.unibg.jarfin.accounting_service.exception;

import jakarta.persistence.EntityNotFoundException;
import org.springframework.http.HttpStatus;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.MethodArgumentNotValidException;
import org.springframework.web.bind.annotation.ExceptionHandler;
import org.springframework.web.bind.annotation.RestControllerAdvice;

import java.util.HashMap;
import java.util.Map;

@RestControllerAdvice
public class GlobalExceptionHandler {

    /**
     * Handle EntityNotFoundExceptions by returning a 404 Not Found response with the exception message.
     *
     * @param ex the EntityNotFoundException to handle
     * @return a ResponseEntity containing the exception message and a 404 status
     */
    @ExceptionHandler(EntityNotFoundException.class)
    public ResponseEntity<String> handleNotFound(EntityNotFoundException ex) {
        return ResponseEntity.status(HttpStatus.NOT_FOUND).body(ex.getMessage());
    }

    /**
     * Handle MethodArgumentNotValidExceptions by returning a 400 Bad Request response with a JSON body containing the field errors.
     * The JSON body will contain key-value pairs where the key is the field name and the value is the default error message associated with the error.
     *
     * @param ex the MethodArgumentNotValidException to handle
     * @return a ResponseEntity containing the field errors and a 400 status
     */
    @ExceptionHandler(MethodArgumentNotValidException.class)
    public ResponseEntity<Map<String, String>> handleValidationErrors(MethodArgumentNotValidException ex) {
        Map<String, String> errors = new HashMap<>();
        ex.getBindingResult().getFieldErrors().forEach(error -> 
            errors.put(error.getField(), error.getDefaultMessage()));
        return ResponseEntity.badRequest().body(errors);
    }
}