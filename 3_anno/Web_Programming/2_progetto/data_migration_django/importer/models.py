# importer/models.py
from django.db import models

class User(models.Model):
    # nomeUtente da MariaDB diventa la PK in Django.
    username = models.CharField(max_length=50, primary_key=True) # Corrisponde a Utente.nomeUtente
    nome = models.CharField(max_length=100)
    cognome = models.CharField(max_length=100)
    email = models.EmailField(max_length=100, unique=True) # Corrisponde a Utente.eMail
    # Se gestisci le password, aggiungi qui un campo models.CharField per l'hash.
    # Esempio: password_hash = models.CharField(max_length=255, blank=True, null=True)

    def __str__(self):
        return self.username

class Quiz(models.Model):
    original_id = models.IntegerField(unique=True) # Per Quiz.codice da MariaDB
    titolo = models.CharField(max_length=255)
    dataInizio = models.DateField()
    dataFine = models.DateField()
    # FK a User.username (che è Utente.nomeUtente)
    creatore = models.ForeignKey(User, on_delete=models.CASCADE, to_field='username', related_name='created_quizzes')

    def __str__(self):
        return f"{self.titolo} (Orig ID: {self.original_id})"

class Question(models.Model):
    # Manteniamo gli ID originali per riferimento e per costruire la FK da Risposta
    original_quiz_id_fk = models.IntegerField() # L'ID originale del Quiz (Quiz.codice)
    original_numero_domanda = models.IntegerField() # Il 'numero' originale della domanda in MariaDB

    # FK al modello Quiz di Django
    quiz = models.ForeignKey(Quiz, on_delete=models.CASCADE, related_name='questions')
    testo = models.TextField()

    class Meta:
        # Assicura che la combinazione di Quiz Django e numero originale della domanda sia unica,
        # o basati sugli ID originali se preferisci una mappatura 1:1 più stretta
        unique_together = (('quiz', 'original_numero_domanda'), ('original_quiz_id_fk', 'original_numero_domanda'))


    def __str__(self):
        return f"Q (Orig QuizID:{self.original_quiz_id_fk}, Orig Num:{self.original_numero_domanda}) - {self.testo[:30]}..."

class Answer(models.Model):
    original_quiz_id_fk = models.IntegerField() # Da Risposta.quiz (che è Domanda.quiz -> Quiz.codice)
    original_domanda_numero_fk = models.IntegerField() # Da Risposta.domanda (che è Domanda.numero)
    original_numero_risposta = models.IntegerField() # Da Risposta.numero

    # FK al modello Question di Django
    question = models.ForeignKey(Question, on_delete=models.CASCADE, related_name='answers')
    testo = models.TextField()
    TIPO_CHOICES = [('Corretta', 'Corretta'), ('Sbagliata', 'Sbagliata')]
    tipo = models.CharField(max_length=10, choices=TIPO_CHOICES)
    punteggio = models.IntegerField(default=0)

    class Meta:
        unique_together = (('question', 'original_numero_risposta'),
                           ('original_quiz_id_fk', 'original_domanda_numero_fk', 'original_numero_risposta'))

    def __str__(self):
        return f"Ans (Orig QZ:{self.original_quiz_id_fk}, QD:{self.original_domanda_numero_fk}, AN:{self.original_numero_risposta}) - {self.testo[:30]}..."

class Participation(models.Model):
    original_id = models.IntegerField(unique=True) # Per Partecipazione.codice
    utente = models.ForeignKey(User, on_delete=models.CASCADE, to_field='username', related_name='participations')
    quiz = models.ForeignKey(Quiz, on_delete=models.CASCADE, related_name='participations_on_quiz') # Evita clash con Quiz.participations
    data = models.DateField()

    def __str__(self):
        return f"Part (Orig ID: {self.original_id}) by {self.utente_id} in Quiz (Orig ID: {self.quiz.original_id})"

class UserQuizAnswer(models.Model):
    # Manteniamo gli ID originali per riferimento
    original_partecipazione_id_fk = models.IntegerField()
    # ID originali che identificano la Risposta scelta in MariaDB
    original_risposta_quiz_id = models.IntegerField()
    original_risposta_domanda_numero = models.IntegerField()
    original_risposta_numero = models.IntegerField()

    partecipazione = models.ForeignKey(Participation, on_delete=models.CASCADE, related_name='given_user_answers')
    chosen_answer = models.ForeignKey(Answer, on_delete=models.CASCADE, related_name='user_choices')

    class Meta:
        unique_together = (('partecipazione', 'chosen_answer'), # Assumendo che un utente dia una sola risposta a una domanda
                           ('original_partecipazione_id_fk', 'original_risposta_quiz_id', 'original_risposta_domanda_numero', 'original_risposta_numero'))

    def __str__(self):
        return f"UserAns for Part (Orig ID: {self.original_partecipazione_id_fk}) to Ans (Orig QZ:{self.original_risposta_quiz_id}, QD:{self.original_risposta_domanda_numero}, AN:{self.original_risposta_numero})"