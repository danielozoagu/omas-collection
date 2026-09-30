using System;
using System.Threading.Tasks;
using System.Windows.Input;

namespace OmasAdminApp.ViewModels
{
    public class RelayCommand : ICommand
    {
        private readonly Func<object?, Task>? _asyncExecuteParam;
        private readonly Action<object?>?     _executeParam;
        private readonly Func<Task>?          _asyncExecute;
        private readonly Action?              _execute;
        private readonly Predicate<object?>?  _canExecuteParam;
        private readonly Func<bool>?          _canExecute;

        public RelayCommand(Func<Task> execute, Func<bool>? canExecute = null)
        {
            _asyncExecute = execute;
            _canExecute = canExecute;
        }

        public RelayCommand(Action execute, Func<bool>? canExecute = null)
        {
            _execute = execute;
            _canExecute = canExecute;
        }

        public RelayCommand(Func<object?, Task> execute, Predicate<object?>? canExecute = null)
        {
            _asyncExecuteParam = execute;
            _canExecuteParam = canExecute;
        }

        public RelayCommand(Action<object?> execute, Predicate<object?>? canExecute = null)
        {
            _executeParam = execute;
            _canExecuteParam = canExecute;
        }

        public bool CanExecute(object? p)
        {
            if (IsBusy) return false;
            if (_canExecute != null) return _canExecute();
            if (_canExecuteParam != null) return _canExecuteParam(p);
            return true;
        }

        public async void Execute(object? p)
        {
            IsBusy = true;
            try
            {
                if (_asyncExecute != null)
                    await _asyncExecute();
                else if (_asyncExecuteParam != null)
                    await _asyncExecuteParam(p);
                else if (_execute != null)
                    _execute();
                else if (_executeParam != null)
                    _executeParam(p);
            }
            finally
            {
                IsBusy = false;
                RaiseCanExecuteChanged();
            }
        }

        private bool _isBusy;
        public bool IsBusy
        {
            get => _isBusy;
            private set
            {
                _isBusy = value;
                RaiseCanExecuteChanged();
            }
        }

        public void RaiseCanExecuteChanged() => CanExecuteChanged?.Invoke(this, EventArgs.Empty);
        public event EventHandler? CanExecuteChanged;
    }
}
