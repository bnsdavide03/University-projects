CXX = g++
CXXFLAGS = -std=c++11 -Wall -O2

SRCS = main.cpp Simulator.cpp
OBJS = $(SRCS:.cpp=.o)
TARGET = simulator

all: $(TARGET)

$(TARGET): $(OBJS)
	$(CXX) $(CXXFLAGS) -o $(TARGET) $(OBJS)

%.o: %.cpp
	$(CXX) $(CXXFLAGS) -c $< -o $@

clean:
	rm -f $(OBJS) $(TARGET)
