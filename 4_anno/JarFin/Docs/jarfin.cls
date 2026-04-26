\NeedsTeXFormat{LaTeX2e}
\ProvidesClass{jarfin}[2026/02/05 JarFin Thesis Class]

% ---------- Classe base ----------
\LoadClass[12pt,a4paper,oneside]{report}

% ---------- Pacchetti obbligatori ----------
\RequirePackage[a4paper, margin=3cm]{geometry}
\RequirePackage{setspace}
\RequirePackage{graphicx}
\RequirePackage{subcaption}
\RequirePackage{amsmath,amssymb}
\RequirePackage{fancyhdr}
\RequirePackage{titlesec}
\RequirePackage{float}
\RequirePackage{booktabs}
\RequirePackage{array}
\RequirePackage{listings}
\RequirePackage[ruled,vlined]{algorithm2e}

\usepackage{xcolor}
\definecolor{primaryBlue}{RGB}{10, 55, 100}
\definecolor{accentGold}{RGB}{200, 150, 50}
\definecolor{codeBg}{RGB}{245, 245, 245}

\usepackage{longtable}
\usepackage[utf8]{inputenc}
\usepackage[T1]{fontenc}
\usepackage{textcomp}
\usepackage{lmodern}
\usepackage[italian]{babel}

\usepackage{tikz}
\usetikzlibrary{shapes.geometric, arrows.meta, positioning, calc}

\lstdefinelanguage{yaml}{
  keywords={true,false,null,y,n},
  keywordstyle=\color{blue}\bfseries,
  basicstyle=\ttfamily\small,
  comment=[l]{\#},
  commentstyle=\color{gray},
  stringstyle=\color{red},
  morestring=[b]',
  morestring=[b]"
}

\lstdefinelanguage{JavaScript}{
  keywords={break, case, catch, continue, debugger, default, delete,
    do, else, finally, for, function, if, in, instanceof, new,
    return, switch, this, throw, try, typeof, var, void, while, with,
    let, const},
  keywordstyle=\color{blue}\bfseries,
  ndkeywords={class, export, boolean, throw, implements, import, this},
  ndkeywordstyle=\color{darkgray}\bfseries,
  identifierstyle=\color{black},
  sensitive=true,
  comment=[l]{//},
  morecomment=[s]{/*}{*/},
  commentstyle=\color{gray}\ttfamily,
  stringstyle=\color{red}\ttfamily,
  morestring=[b]',
  morestring=[b]"
}

% ---------- Spaziatura ----------
\onehalfspacing
\setlength{\parindent}{0pt}
\setlength{\parskip}{4pt}

% ---------- Header / Footer ----------
\pagestyle{fancy}
\fancyhf{}

\renewcommand{\headrulewidth}{0pt} 

\fancyfoot[C]{\thepage}

% ---------- Formato capitoli ----------
\titleformat{\chapter}[display]
{\normalfont\huge\bfseries\color{black}}
{\chaptertitlename\ \thechapter}{20pt}{\Huge}

% ---------- Listings ----------
\lstset{
    backgroundcolor=\color{codeBg},
    basicstyle=\ttfamily\small,
    frame=single,
    breaklines=true,
    numbers=left,
    numberstyle=\tiny,
    keywordstyle=\color{black}\bfseries
}

% ---------- Fix Compatibilità ----------
\providecommand{\textLaTeX}{\LaTeX}

% ---------- Hyperref ----------
\RequirePackage{hyperref}

\hypersetup{
    colorlinks=true,
    linkcolor=black,
    citecolor=black,
    urlcolor=primaryBlue
}

\endinput