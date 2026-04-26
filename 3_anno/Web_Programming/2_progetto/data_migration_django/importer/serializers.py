# importer/serializers.py
from rest_framework import serializers
from .models import User, Quiz, Question, Answer, Participation, UserQuizAnswer

class UserImportSerializer(serializers.ModelSerializer):
    class Meta: model = User; fields = '__all__'
class QuizImportSerializer(serializers.ModelSerializer):
    class Meta: model = Quiz; fields = '__all__'
class QuestionImportSerializer(serializers.ModelSerializer):
    class Meta: model = Question; fields = '__all__'
class AnswerImportSerializer(serializers.ModelSerializer):
    class Meta: model = Answer; fields = '__all__'
class ParticipationImportSerializer(serializers.ModelSerializer):
    class Meta: model = Participation; fields = '__all__'
class UserQuizAnswerImportSerializer(serializers.ModelSerializer):
    class Meta: model = UserQuizAnswer; fields = '__all__'