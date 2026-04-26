# importer/views.py
from rest_framework import status
from rest_framework.response import Response
from rest_framework.views import APIView
from django.db import transaction
from .models import User, Quiz, Question, Answer, Participation, UserQuizAnswer

# Mappa globale per tracciare gli ID originali di MySQL e le loro corrispondenti PK di Django.
# Le chiavi per 'questions' e 'answers' saranno tuple per rappresentare le PK composite originali.
original_to_django_ids = {
    'users': {},         # 'mysql_username' -> django_user_pk (che è User.username)
    'quizzes': {},       # mysql_quiz_codice -> django_quiz_pk (Quiz.pk)
    'questions': {},     # (mysql_quiz_codice, mysql_domanda_numero) -> django_question_pk (Question.pk)
    'answers': {},       # (mysql_quiz_codice, mysql_domanda_numero, mysql_risposta_numero) -> django_answer_pk (Answer.pk)
    'participations': {} # mysql_partecipazione_codice -> django_partecipation_pk (Participation.pk)
}

class ClearIdMapView(APIView):
    """
    Pulisce la mappa globale original_to_django_ids.
    Utile se si deve rieseguire la migrazione da zero.
    """
    def post(self, request, *args, **kwargs):
        global original_to_django_ids
        for key in original_to_django_ids:
            original_to_django_ids[key] = {}
        # Nota: In un ambiente di produzione con più worker, questa variabile globale
        # non sarebbe condivisa. Per una migrazione singola, va bene.
        # Per scenari più complessi, si userebbe Redis, un DB temporaneo, o file.
        return Response({"message": "ID map cleared successfully."}, status=status.HTTP_200_OK)


class UserImportView(APIView):
    def post(self, request, *args, **kwargs):
        data_list = request.data if isinstance(request.data, list) else [request.data]
        created_count = 0
        updated_count = 0
        errors = []

        with transaction.atomic():
            for item_data in data_list:
                try:
                    # PHP JSON: original_id (nomeUtente), nome, cognome, eMail
                    # Django Model: username (PK), nome, cognome, email
                    original_username = item_data.get('original_id') # Questo è Utente.nomeUtente

                    if not original_username:
                        errors.append({'error': "Missing 'original_id' (username) in item.", 'data': item_data})
                        continue

                    defaults_payload = {
                        'nome': item_data.get('nome'),
                        'cognome': item_data.get('cognome'),
                        'email': item_data.get('eMail') # Nota: PHP manda 'eMail', Django usa 'email'
                    }
                    
                    # User.username è la PK e corrisponde a original_username
                    user_obj, created = User.objects.update_or_create(
                        username=original_username, # Campo di lookup (PK)
                        defaults=defaults_payload
                    )

                    # Non è necessario popolare original_to_django_ids['users'] perché
                    # original_username È la PK Django (User.username).
                    # Se User avesse un 'id' AutoField e 'original_id' fosse un campo separato,
                    # allora popoleremmo la mappa. Per coerenza con gli altri, possiamo farlo:
                    original_to_django_ids['users'][original_username] = user_obj.pk # user_obj.pk == original_username

                    if created: created_count += 1
                    else: updated_count += 1

                except Exception as e:
                    errors.append({'error': str(e), 'data': item_data})
        
        if errors:
            return Response({
                'message': 'User import completed with errors.', 'created': created_count, 'updated': updated_count, 'errors': errors
            }, status=status.HTTP_400_BAD_REQUEST)
        return Response({
            'message': 'User import completed successfully.', 'created': created_count, 'updated': updated_count
        }, status=status.HTTP_201_CREATED)


class QuizImportView(APIView):
    def post(self, request, *args, **kwargs):
        data_list = request.data if isinstance(request.data, list) else [request.data]
        created_count = 0
        updated_count = 0
        errors = []

        with transaction.atomic():
            for item_data in data_list:
                try:
                    # PHP JSON: original_id (Quiz.codice), titolo, dataInizio, dataFine, creatore_original_id (Utente.nomeUtente)
                    # Django Model: original_id, titolo, dataInizio, dataFine, creatore (FK a User)
                    quiz_original_id = item_data.get('original_id') # Quiz.codice da MySQL
                    creator_original_username = item_data.get('creatore_original_id')

                    if quiz_original_id is None: # Controlla anche 0 se non è valido
                        errors.append({'error': "Missing 'original_id' for Quiz.", 'data': item_data})
                        continue
                    
                    django_creator_pk = None
                    if creator_original_username:
                        # User.username è la PK di User, quindi la mappa 'users' usa username come chiave
                        django_creator_pk = original_to_django_ids['users'].get(creator_original_username)
                        if not django_creator_pk: # django_creator_pk sarà lo stesso username
                            errors.append({'error': f"Creator User '{creator_original_username}' not found in ID map for Quiz {quiz_original_id}.", 'data': item_data})
                            continue
                    
                    defaults_payload = {
                        'titolo': item_data.get('titolo'),
                        'dataInizio': item_data.get('dataInizio'),
                        'dataFine': item_data.get('dataFine'),
                        'creatore_id': django_creator_pk # Assegna la PK (username) di User
                    }
                    
                    quiz_obj, created = Quiz.objects.update_or_create(
                        original_id=quiz_original_id, # Lookup field
                        defaults=defaults_payload
                    )
                    
                    original_to_django_ids['quizzes'][quiz_original_id] = quiz_obj.pk
                    
                    if created: created_count += 1
                    else: updated_count += 1
                except Exception as e:
                    errors.append({'error': str(e), 'data': item_data})

        if errors: return Response({'message': 'Quiz import completed with errors.', 'created': created_count, 'updated': updated_count, 'errors': errors}, status=status.HTTP_400_BAD_REQUEST)
        return Response({'message': 'Quiz import completed successfully.', 'created': created_count, 'updated': updated_count}, status=status.HTTP_201_CREATED)


class QuestionImportView(APIView):
    def post(self, request, *args, **kwargs):
        data_list = request.data if isinstance(request.data, list) else [request.data]
        created_count = 0
        updated_count = 0
        errors = []

        with transaction.atomic():
            for item_data in data_list:
                try:
                    # PHP JSON: quiz_original_id, numero_originale_domanda, testo
                    # Django Model: original_quiz_id_fk, original_numero_domanda, quiz (FK), testo
                    mysql_quiz_id = item_data.get('quiz_original_id')
                    mysql_question_num = item_data.get('numero_originale_domanda')

                    if mysql_quiz_id is None or mysql_question_num is None:
                        errors.append({'error': "Missing 'quiz_original_id' or 'numero_originale_domanda'.", 'data': item_data})
                        continue
                    
                    question_original_key_tuple = (mysql_quiz_id, mysql_question_num)

                    django_quiz_pk = original_to_django_ids['quizzes'].get(mysql_quiz_id)
                    if not django_quiz_pk:
                        errors.append({'error': f"Quiz (Original ID: {mysql_quiz_id}) not found in ID map for Question {question_original_key_tuple}.", 'data': item_data})
                        continue

                    defaults_payload = {
                        'testo': item_data.get('testo'),
                        'quiz_id': django_quiz_pk # FK all'oggetto Quiz Django
                        # 'original_quiz_id_fk' e 'original_numero_domanda' sono usati per il lookup sotto
                    }
                    
                    # unique_together = (..., ('original_quiz_id_fk', 'original_numero_domanda'))
                    question_obj, created = Question.objects.update_or_create(
                        original_quiz_id_fk=mysql_quiz_id,         # Lookup field 1
                        original_numero_domanda=mysql_question_num, # Lookup field 2
                        defaults=defaults_payload
                    )
                    
                    original_to_django_ids['questions'][question_original_key_tuple] = question_obj.pk
                    
                    if created: created_count += 1
                    else: updated_count += 1
                except Exception as e:
                    errors.append({'error': str(e), 'data': item_data})

        if errors: return Response({'message': 'Question import completed with errors.', 'created': created_count, 'updated': updated_count, 'errors': errors}, status=status.HTTP_400_BAD_REQUEST)
        return Response({'message': 'Question import completed successfully.', 'created': created_count, 'updated': updated_count}, status=status.HTTP_201_CREATED)


class AnswerImportView(APIView):
    def post(self, request, *args, **kwargs):
        data_list = request.data if isinstance(request.data, list) else [request.data]
        created_count = 0
        updated_count = 0
        errors = []

        with transaction.atomic():
            for item_data in data_list:
                try:
                    # PHP JSON: quiz_original_id, domanda_original_numero, numero_originale_risposta, testo, tipo, punteggio
                    # Django Model: original_quiz_id_fk, original_domanda_numero_fk, original_numero_risposta, question (FK), testo, tipo, punteggio
                    mysql_quiz_id = item_data.get('quiz_original_id')
                    mysql_question_num = item_data.get('domanda_original_numero')
                    mysql_answer_num = item_data.get('numero_originale_risposta')

                    if mysql_quiz_id is None or mysql_question_num is None or mysql_answer_num is None:
                        errors.append({'error': "Missing original IDs for Answer.", 'data': item_data})
                        continue
                    
                    answer_original_key_tuple = (mysql_quiz_id, mysql_question_num, mysql_answer_num)
                    original_question_key_for_fk_lookup = (mysql_quiz_id, mysql_question_num)

                    django_question_pk = original_to_django_ids['questions'].get(original_question_key_for_fk_lookup)
                    if not django_question_pk:
                        errors.append({'error': f"Question {original_question_key_for_fk_lookup} not found in ID map for Answer {answer_original_key_tuple}.", 'data': item_data})
                        continue
                        
                    defaults_payload = {
                        'testo': item_data.get('testo'),
                        'tipo': item_data.get('tipo'),
                        'punteggio': item_data.get('punteggio', 0), # Default se mancante
                        'question_id': django_question_pk
                    }
                    
                    # unique_together = (..., ('original_quiz_id_fk', 'original_domanda_numero_fk', 'original_numero_risposta'))
                    answer_obj, created = Answer.objects.update_or_create(
                        original_quiz_id_fk=mysql_quiz_id,
                        original_domanda_numero_fk=mysql_question_num,
                        original_numero_risposta=mysql_answer_num,
                        defaults=defaults_payload
                    )
                    
                    original_to_django_ids['answers'][answer_original_key_tuple] = answer_obj.pk
                    
                    if created: created_count += 1
                    else: updated_count += 1
                except Exception as e:
                    errors.append({'error': str(e), 'data': item_data})

        if errors: return Response({'message': 'Answer import completed with errors.', 'created': created_count, 'updated': updated_count, 'errors': errors}, status=status.HTTP_400_BAD_REQUEST)
        return Response({'message': 'Answer import completed successfully.', 'created': created_count, 'updated': updated_count}, status=status.HTTP_201_CREATED)


class ParticipationImportView(APIView):
    def post(self, request, *args, **kwargs):
        data_list = request.data if isinstance(request.data, list) else [request.data]
        created_count = 0
        updated_count = 0
        errors = []

        with transaction.atomic():
            for item_data in data_list:
                try:
                    # PHP JSON: original_id (Partecipazione.codice), utente_original_id (Utente.nomeUtente), quiz_original_id (Quiz.codice), data
                    # Django Model: original_id, utente (FK), quiz (FK), data
                    part_original_id = item_data.get('original_id') # Partecipazione.codice
                    user_original_username = item_data.get('utente_original_id')
                    quiz_original_id_fk = item_data.get('quiz_original_id')

                    if part_original_id is None:
                        errors.append({'error': "Missing 'original_id' for Participation.", 'data': item_data})
                        continue
                    
                    django_user_pk = None
                    if user_original_username:
                        django_user_pk = original_to_django_ids['users'].get(user_original_username)
                        if not django_user_pk:
                            errors.append({'error': f"User '{user_original_username}' not found for Participation {part_original_id}.", 'data': item_data})
                            continue
                    
                    django_quiz_pk = None
                    if quiz_original_id_fk is not None:
                        django_quiz_pk = original_to_django_ids['quizzes'].get(quiz_original_id_fk)
                        if not django_quiz_pk:
                            errors.append({'error': f"Quiz (Original ID: {quiz_original_id_fk}) not found for Participation {part_original_id}.", 'data': item_data})
                            continue
                    
                    defaults_payload = {
                        'data': item_data.get('data'),
                        'utente_id': django_user_pk,
                        'quiz_id': django_quiz_pk
                    }
                    
                    part_obj, created = Participation.objects.update_or_create(
                        original_id=part_original_id, # Lookup field
                        defaults=defaults_payload
                    )
                    
                    original_to_django_ids['participations'][part_original_id] = part_obj.pk
                    
                    if created: created_count += 1
                    else: updated_count += 1
                except Exception as e:
                    errors.append({'error': str(e), 'data': item_data})

        if errors: return Response({'message': 'Participation import completed with errors.', 'created': created_count, 'updated': updated_count, 'errors': errors}, status=status.HTTP_400_BAD_REQUEST)
        return Response({'message': 'Participation import completed successfully.', 'created': created_count, 'updated': updated_count}, status=status.HTTP_201_CREATED)


class UserQuizAnswerImportView(APIView):
    def post(self, request, *args, **kwargs):
        data_list = request.data if isinstance(request.data, list) else [request.data]
        created_count = 0
        updated_count = 0
        errors = []

        with transaction.atomic():
            for item_data in data_list:
                try:
                    # PHP JSON: partecipazione_original_id, 
                    #           risposta_original_quiz_id, risposta_original_domanda_numero, risposta_original_numero
                    # Django Model: original_partecipazione_id_fk, original_risposta_quiz_id, ... , partecipazione (FK), chosen_answer (FK)
                    
                    mysql_part_id = item_data.get('partecipazione_original_id')
                    mysql_ans_quiz_id = item_data.get('risposta_original_quiz_id')
                    mysql_ans_q_num = item_data.get('risposta_original_domanda_numero')
                    mysql_ans_num = item_data.get('risposta_original_numero')

                    if mysql_part_id is None or mysql_ans_quiz_id is None or mysql_ans_q_num is None or mysql_ans_num is None:
                        errors.append({'error': "Missing original IDs for UserQuizAnswer.", 'data': item_data})
                        continue

                    # Chiave per identificare univocamente questa riga UserQuizAnswer in base agli ID originali
                    user_quiz_answer_original_key = (mysql_part_id, mysql_ans_quiz_id, mysql_ans_q_num, mysql_ans_num)

                    # Risolvi FK per Participation
                    django_participation_pk = original_to_django_ids['participations'].get(mysql_part_id)
                    if not django_participation_pk:
                        errors.append({'error': f"Participation (Original ID: {mysql_part_id}) not found for UserQuizAnswer.", 'data': item_data})
                        continue

                    # Risolvi FK per Answer
                    original_answer_key_for_fk_lookup = (mysql_ans_quiz_id, mysql_ans_q_num, mysql_ans_num)
                    django_answer_pk = original_to_django_ids['answers'].get(original_answer_key_for_fk_lookup)
                    if not django_answer_pk:
                        errors.append({'error': f"Answer {original_answer_key_for_fk_lookup} not found for UserQuizAnswer.", 'data': item_data})
                        continue
                        
                    defaults_payload = {
                        'partecipazione_id': django_participation_pk,
                        'chosen_answer_id': django_answer_pk
                        # I campi original_*_fk sono usati per il lookup
                    }
                    
                    # unique_together = (..., ('original_partecipazione_id_fk', 'original_risposta_quiz_id', 'original_risposta_domanda_numero', 'original_risposta_numero'))
                    user_quiz_answer_obj, created = UserQuizAnswer.objects.update_or_create(
                        original_partecipazione_id_fk=mysql_part_id,
                        original_risposta_quiz_id=mysql_ans_quiz_id,
                        original_risposta_domanda_numero=mysql_ans_q_num,
                        original_risposta_numero=mysql_ans_num,
                        defaults=defaults_payload
                    )
                    
                    # Questa tabella di solito non è referenziata da altre, quindi non è strettamente
                    # necessario popolarla in original_to_django_ids, a meno che non serva per debug.
                    
                    if created: created_count += 1
                    else: updated_count += 1
                except Exception as e:
                    errors.append({'error': str(e), 'data': item_data})

        if errors: return Response({'message': 'UserQuizAnswer import completed with errors.', 'created': created_count, 'updated': updated_count, 'errors': errors}, status=status.HTTP_400_BAD_REQUEST)
        return Response({'message': 'UserQuizAnswer import completed successfully.', 'created': created_count, 'updated': updated_count}, status=status.HTTP_201_CREATED)