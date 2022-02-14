$(document).ready(function () {


function _toConsumableArray(arr) {
    return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _unsupportedIterableToArray(arr) || _nonIterableSpread();
}

function _nonIterableSpread() {
    throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.");
}

function _unsupportedIterableToArray(o, minLen) {
    if (!o)
        return;
    if (typeof o === "string")
        return _arrayLikeToArray(o, minLen);
    var n = Object.prototype.toString.call(o).slice(8, -1);
    if (n === "Object" && o.constructor)
        n = o.constructor.name;
    if (n === "Map" || n === "Set")
        return Array.from(o);
    if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n))
        return _arrayLikeToArray(o, minLen);
}

function _iterableToArray(iter) {
    if (typeof Symbol !== "undefined" && iter[Symbol.iterator] != null || iter["@@iterator"] != null)
        return Array.from(iter);
}

function _arrayWithoutHoles(arr) {
    if (Array.isArray(arr))
        return _arrayLikeToArray(arr);
}

function _arrayLikeToArray(arr, len) {
    if (len == null || len > arr.length)
        len = arr.length;
    for (var i = 0, arr2 = new Array(len); i < len; i++) {
        arr2[i] = arr[i];
    }
    return arr2;
}


var animateCSS = function animateCSS(element, animation) {
    var prefix = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 'animate__';
    return (
        new Promise(function (resolve, reject) {
            var animationName = "".concat(prefix).concat(animation);
            element.classList.add("".concat(prefix, "animated"), animationName);
            function handleAnimationEnd(event) {
                event.stopPropagation();
                element.classList.remove("".concat(prefix, "animated"), animationName);
                resolve('Animation ended');
            }

            element.addEventListener('animationend', handleAnimationEnd, {
                once: true
            });
        })
    );
};

var nTrigger = document.querySelector('.js--navigation-trigger');
var nClose = document.querySelector('.js--navigation-close');
var nTarget = nTrigger.nextElementSibling;

if (window.innerWidth < 1200) {
    var addTargets = nTarget.dataset.add.split(',');

    addTargets.forEach(function (addTarget) {
        addTarget = document.querySelector(addTarget);
        nTarget.append(addTarget);
    });
    nTrigger.addEventListener('click', function (e) {
        e.preventDefault();
        e.target.nextElementSibling.style.display = 'flex';
    });
    nClose.addEventListener('click', function (e) {
        e.preventDefault();
        e.target.parentElement.style.display = 'none';
    });
}

var triggers = document.querySelectorAll('.js--menu-trigger');
var opened = document.querySelectorAll('.js--menu-trigger .--open');

if (window.innerWidth > 1380) {
    triggers.forEach(function (t) {
        var classes = t.dataset.classes;
        t.addEventListener('mouseenter', function (s) {
            _toConsumableArray(s.target.parentElement.children).forEach(function (el) {
                if (el.querySelector('div') != null) {
                    el.querySelector('div').classList.remove('--open');
                    el.querySelector('a').classList.remove('--active');
                }
            });

            if (s.target.querySelector('div') != null) {
                s.target.querySelector('div').classList.add('--open');
                s.target.querySelector('a').classList.add('--active');
            }

            if (classes != undefined) {
                var _s$target$querySelect;

                (_s$target$querySelect = s.target.querySelector('div').classList).add.apply(_s$target$querySelect, _toConsumableArray(classes.split(" ")));
            }

            if (s.target.dataset.animation != undefined) {
                animateCSS(s.target.querySelector('div'), s.target.dataset.animation);
            }
        });

        t.addEventListener('mouseleave', function (s) {
            if (s.target.querySelector('div') != null) {
                s.target.querySelector('div').classList.remove('--open');
                s.target.querySelector('a').classList.remove('--active');
            }

            if (classes != undefined) {
                var _s$target$querySelect2;

                (_s$target$querySelect2 = s.target.querySelector('div').classList).remove.apply(_s$target$querySelect2, _toConsumableArray(classes.split(" ")));
            }

            opened.forEach(function (el) {
                el.classList.add('--open');
                el.parentElement.querySelector('a').classList.add('--active');
            });
        });
    });
} else {
    var mobileTriggers = [];
    triggers.forEach(function (el, i) {
        if (el.querySelector('div') != null) {
            var mobileLink = document.createElement('span');
            mobileLink.classList.add('__after');
            mobileTriggers[i] = mobileLink;
            el.querySelector('a').append(mobileLink);
        }
    });

    opened.forEach(function (el) {
        el.classList.remove('--open');
        el.parentElement.querySelector('a').classList.remove('--active');
    });

    mobileTriggers.forEach(function (t) {
        t.addEventListener('click', function (s) {
            var current = s.target.parentElement.parentElement;
            var condition = current.querySelector('div').classList.contains('--open');
            var classes = current.dataset.classes;
            s.preventDefault();
            s.stopPropagation();

            _toConsumableArray(current.parentElement.children).forEach(function (el) {
                if (el.querySelector('div') != null) {
                    el.querySelector('div').classList.remove('--open');
                    el.querySelector('a').classList.remove('--active');
                }
            });

            if (condition) {
                current.querySelector('div').classList.remove('--open');
                current.querySelector('a').classList.remove('--active');

                if (classes != undefined) {
                    var _current$querySelecto;

                    (_current$querySelecto = current.querySelector('div').classList).remove.apply(_current$querySelecto, _toConsumableArray(classes.split(" ")));
                }
            } else {
                current.querySelector('div').classList.add('--open');
                current.querySelector('a').classList.add('--active');

                if (classes != undefined) {
                    var _current$querySelecto2;

                    (_current$querySelecto2 = current.querySelector('div').classList).add.apply(_current$querySelecto2, _toConsumableArray(classes.split(" ")));
                }
            }
        });

        document.addEventListener('click', function (s) {
            triggers.forEach(function (el) {
                if (el.querySelector('div') != null) {
                    el.querySelector('div').classList.remove('--open');
                    el.querySelector('a').classList.remove('--active');
                }
            });
        });
    });
}

var accordions = document.querySelectorAll('.js--accordion-trigger');
var accordionMobileTriggers = [];

accordions.forEach(function (el, i) {
    if (el.querySelector('div') != null) {
        var mobileLink = document.createElement('span');
        mobileLink.classList.add('__after');
        accordionMobileTriggers[i] = mobileLink;
        el.querySelector('a').append(mobileLink);
    }
});

accordionMobileTriggers.forEach(function (t) {
    t.addEventListener('click', function (s) {
        var current = s.target.parentElement.parentElement;
        var condition = current.querySelector('a').classList.contains('--active');
        s.preventDefault();
        s.stopPropagation();
        accordionMobileTriggers.forEach(function (el) {
            if (el.parentElement.parentElement.querySelector('div') != null) {
                el.parentElement.parentElement.querySelector('div').classList.remove('--open');
            }
            el.parentElement.parentElement.querySelector('a').classList.remove('--active');
        });

        if (condition) {
            if (current.querySelector('div') != null) {
                current.querySelector('div').classList.remove('--open');
            }
            current.querySelector('a').classList.remove('--active');
        } else {
            if (current.querySelector('div') != null) {
                current.querySelector('div').classList.add('--open');
            }
            current.querySelector('a').classList.add('--active');
        }
    });
});

});