# data_migrator/importer/urls.py
from django.contrib import admin
from django.urls import path
from .views import (
    UserImportView, QuizImportView, QuestionImportView, AnswerImportView,
    ParticipationImportView, UserQuizAnswerImportView, ClearIdMapView
)

urlpatterns = [
    # Endpoint per pulire la mappa degli ID originali -> Django PK
    path('clear_id_map/', ClearIdMapView.as_view(), name='clear-id-map'),
    # Endpoint per importare ciascuna entità
    path('users/', UserImportView.as_view(), name='import-users'),
    path('quizzes/', QuizImportView.as_view(), name='import-quizzes'),
    path('questions/', QuestionImportView.as_view(), name='import-questions'),
    path('answers/', AnswerImportView.as_view(), name='import-answers'),
    path('participations/', ParticipationImportView.as_view(), name='import-participations'),
    path('user_quiz_answers/', UserQuizAnswerImportView.as_view(), name='import-user-quiz-answers'),
]