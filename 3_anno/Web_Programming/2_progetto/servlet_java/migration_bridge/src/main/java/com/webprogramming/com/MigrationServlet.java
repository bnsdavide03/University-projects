package com.webprogramming.com;

import java.io.IOException;
import java.io.InputStream;
import java.io.PrintWriter;
import java.net.URI;
import java.net.http.HttpClient;
import java.net.http.HttpRequest;
import java.net.http.HttpResponse;

import jakarta.servlet.ServletConfig;
import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;

import com.fasterxml.jackson.databind.JsonNode;
import com.fasterxml.jackson.databind.ObjectMapper;
import com.fasterxml.jackson.databind.node.ArrayNode;

@WebServlet("/migrate")
public class MigrationServlet extends HttpServlet {

    private static final String PHP_BASE_URL = "http://152.67.76.115/migration_api/";
    private static final String DJANGO_BASE_URL = "http://127.0.0.1:8000/api/importers/";

    private final HttpClient httpClient = HttpClient.newBuilder().version(HttpClient.Version.HTTP_2).build();
    private final ObjectMapper objectMapper = new ObjectMapper();

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {        
        resp.setContentType("text/plain;charset=UTF-8");
        StringBuilder log = new StringBuilder();
        PrintWriter out = resp.getWriter(); // Usiamo PrintWriter per e.printStackTrace

        log.append("Avvio processo di migrazione...\n\n");

        log.append("Pulizia mappa ID Django (clear_id_map)...\n");
        try {
            String clearMapResponse = postToDjango(DJANGO_BASE_URL + "clear_id_map/", "{}");
            log.append("Risposta da clear_id_map: ").append(clearMapResponse).append("\n\n");
        } catch (IOException e) { // IOException è già dichiarata da doGet
            log.append("ERRORE IO durante la chiamata a clear_id_map: ").append(e.getMessage()).append("\n\n");
            e.printStackTrace(out);
        } catch (InterruptedException e) { // Cattura InterruptedException qui
            log.append("Thread INTERROTTO durante la chiamata a clear_id_map: ").append(e.getMessage()).append("\n\n");
            Thread.currentThread().interrupt();
            e.printStackTrace(out);
            // Potresti voler terminare qui o gestire diversamente l'interruzione
        }


        String[] entities = {"users", "quizzes", "questions", "answers", "participations", "user_quiz_answers"};
        String[] phpScripts = {"export_users.php", "export_quizzes.php", "export_questions.php", "export_answers.php", "export_participations.php", "export_user_quiz_answers.php"};
        String[] djangoEndpoints = {"users/", "quizzes/", "questions/", "answers/", "participations/", "user_quiz_answers/"};


        for (int i = 0; i < entities.length; i++) {
            String entity = entities[i];
            String phpScript = phpScripts[i];
            String djangoEndpoint = djangoEndpoints[i];

            log.append("Migrazione di: ").append(entity).append("...\n");

            try {
                String phpUrl = PHP_BASE_URL + phpScript;
                log.append("  Lettura da PHP: ").append(phpUrl).append("\n");

                HttpRequest getRequest = HttpRequest.newBuilder()
                        .uri(URI.create(phpUrl))
                        .GET()
                        .build();
                
                HttpResponse<InputStream> phpResponse = httpClient.send(getRequest, HttpResponse.BodyHandlers.ofInputStream()); // Può lanciare IOException e InterruptedException

                if (phpResponse.statusCode() == 200) {
                    JsonNode rootNode = objectMapper.readTree(phpResponse.body());
                    if (rootNode.path("success").asBoolean(false)) {
                        ArrayNode dataArray = (ArrayNode) rootNode.path("data");
                        if (dataArray != null && dataArray.size() > 0) {
                            String jsonData = objectMapper.writeValueAsString(dataArray);
                            log.append("    Letti ").append(dataArray.size()).append(" record(s) da PHP.\n");

                            String djangoUrl = DJANGO_BASE_URL + djangoEndpoint;
                            log.append("    Invio a Django: ").append(djangoUrl).append("\n");
                            
                            String djangoResponse = postToDjango(djangoUrl, jsonData); // Può lanciare IOException e InterruptedException
                            log.append("    Risposta da Django: ").append(djangoResponse).append("\n");
                        } else {
                            log.append("    Nessun dato da migrare per ").append(entity).append(" (o il campo 'data' è mancante/vuoto).\n");
                        }
                    } else {
                         log.append("    Errore dal servizio PHP per ").append(entity).append(": ")
                            .append(rootNode.path("message").asText("Errore sconosciuto o messaggio mancante dal servizio PHP."))
                            .append("\n");
                    }
                } else {
                    log.append("    Errore durante la lettura da PHP (HTTP Status: ").append(phpResponse.statusCode()).append(").\n");
                    String errorBody = "";
                    try (InputStream errorStream = phpResponse.body()) {
                        errorBody = new String(errorStream.readAllBytes());
                    } catch (IOException readEx) {
                        log.append("      Errore durante la lettura del corpo della risposta di errore PHP: ").append(readEx.getMessage()).append("\n");
                    }
                    log.append("    Corpo della risposta di errore PHP: ").append(errorBody).append("\n");
                }

            } catch (IOException e) { // IOException è già dichiarata da doGet
                log.append("  ERRORE IO durante la migrazione di ").append(entity).append(": ").append(e.getMessage()).append("\n");
                e.printStackTrace(out);
            } catch (InterruptedException e) { // Cattura InterruptedException qui
                log.append("  Thread INTERROTTO durante la migrazione di ").append(entity).append(": ").append(e.getMessage()).append("\n");
                Thread.currentThread().interrupt();
                e.printStackTrace(out);
                break; 
            } catch (Exception e) {
                log.append("  ERRORE GENERICO durante la migrazione di ").append(entity).append(": ").append(e.getMessage()).append("\n");
                e.printStackTrace(out);
            }
            log.append("\n");
        }

        log.append("Processo di migrazione terminato.\n");
        out.write(log.toString()); // Usa PrintWriter per scrivere nella risposta
    }

    // Questo metodo può ancora dichiarare InterruptedException perché non è un override
    private String postToDjango(String url, String jsonPayload) throws IOException, InterruptedException {
        HttpRequest postRequest = HttpRequest.newBuilder()
                .uri(URI.create(url))
                .POST(HttpRequest.BodyPublishers.ofString(jsonPayload))
                .header("Content-Type", "application/json")
                .build();
        
        HttpResponse<String> djangoResponse = httpClient.send(postRequest, HttpResponse.BodyHandlers.ofString());
        return "Status: " + djangoResponse.statusCode() + ", Body: " + djangoResponse.body();
    }
}